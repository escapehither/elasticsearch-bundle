<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 01/11/16
 * Time: 22:58
 */

namespace EscapeHither\SearchManagerBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Index;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\EsClient;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Index delete and update Es Document.
 * Class IndexerListener
 * @package EscapeHither\SearchManagerBundle\EventListener
 */
class IndexerListener
{
    private $container;
    private const INDEX_NAME = 'index_name';
    private const FIELD_NAME = 'fieldName';

    /**
     * Index Listener Constructor.
     *
     * @param array           $indexes  The indexes
     * @param string          $host     Es host.
     * @param ManagerRegistry $doctrine Doctrine registry.
     */
    public function __construct($indexes, $host, ManagerRegistry $doctrine)
    {
        $this->indexes = $indexes;
        $this->host = $host;
        $this->doctrine = $doctrine;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $class = get_class($object);

        if ($this->indexHasParameter($class)) {
            $this->indexDocument($class, $object);
        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $class = get_class($object);

        if ($this->indexHasParameter($class)) {
            $this->indexDocument($class, $object);
        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $class = get_class($object);

        if ($this->indexHasParameter($class)) {
            $parameter = $this->indexes[$class];
            $this->getIndex($parameter[self::INDEX_NAME])->deleteDocument($parameter['type'], $object->getId());
        }
    }

    /**
     * @param $class
     * @param $object
     */
    protected function indexDocument($class, $object)
    {
        $parameter = $this->indexes[$class];
        $documentHandler = new DocumentHandler($object, $parameter);
        $document = $documentHandler->CreateDocument();

        $fieldMappings = $this->getEntityMetadataFieldMappings($class);
        $mapping[$document->getType()] = [];

        foreach ($fieldMappings as $key => $value) {
            if ('string' === $value['type']) {
                $mapping[$document->getType()]['properties'][$value[self::FIELD_NAME]] = $this->getDefaultStringAnalyzer();
            }
        }

        $document->setMapping($mapping);
        $document->setId($object->getId());
        $this->getIndex($parameter[self::INDEX_NAME])->indexDocument($document);
    }

    /**
     * @param $indexName
     * @return Index
     */
    protected function getIndex($indexName)
    {
        return $index = new Index($indexName, new EsClient($this->host));
    }


    /**
     * @param $entity
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function getEntityMetadataFieldMappings($entity)
    {
        // TODO ADD CASHING
        $factory = new DisconnectedMetadataFactory($this->doctrine);
        $metadataClass = $factory->getClassMetadata($entity)->getMetadata()[0];
        $baseMapping = $metadataClass->fieldMappings;

        foreach ($metadataClass->associationMappings as $fieldAssociation => $association) {
            $metadataAssociation = $factory->getClassMetadata($association['targetEntity'])->getMetadata()[0];
            $mappingAssociation = $metadataAssociation->fieldMappings;

            foreach ($mappingAssociation as $keyMapping => $mapping) {
                $mapping[s] = $fieldAssociation.'.'.$mapping[self::FIELD_NAME];
                $baseMapping[$mapping[self::FIELD_NAME]] = $mapping ;
            }
        }

        return $baseMapping;
    }

    /**
     * @return array
     */
    protected function getDefaultStringAnalyzer()
    {
        return $default = [
            'type' => 'string',
            'analyzer' => 'standard',
            'fields' => [
            'asciifolding' => [
                'type' => 'string',
                'analyzer' => 'folding_analyzer',
            ],
            'exact_value' => [
                'type' => 'string',
                'index' => 'not_analyzed',
            ],
            ],
        ];
    }
    /**
     * Get parameter Class.
     *
     * @param string $class The resource class.
     *
     * @return string
     */
    protected function getParameterClass($class)
    {
        return $class;
    }

    /**
     * Index has parameter.
     *
     * @param string $class The resource class.
     *
     * @return bool
     */
    protected function indexHasParameter($class)
    {
        return array_key_exists($class, $this->indexes);
    }
}

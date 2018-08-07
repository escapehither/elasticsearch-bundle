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

/**
 * Index delete and update Es Document.
 * Class IndexerListener
 * @package EscapeHither\SearchManagerBundle\EventListener
 */
class IndexerListener
{
    private $container;
    const INDEX_NAME = 'index_name';
    const HOST_NAME = 'escape_hither_search_manager.host';


    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $class = get_class($object);
        if ($this->container->hasParameter($class)) {
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
        if ($this->container->hasParameter($class)) {
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
        if ($this->container->hasParameter($class)) {
            $parameter = $this->container->getParameter($class);
            $this->getIndex($parameter[self::INDEX_NAME])->deleteDocument($parameter['type'], $object->getId());
        }
    }

    /**
     * @param $class
     * @param $object
     */
    protected function indexDocument($class, $object)
    {
        $parameter = $this->container->getParameter($class);
        $documentHandler = new DocumentHandler($object, $parameter);
        $document = $documentHandler->CreateDocument();

        $fieldMappings = $this->getEntityMetadataFieldMappings($class);
        //dump($class);
        $mapping[$document->getType()] = [];
        //dump($fieldMappings);
        //die('ok');
        foreach ($fieldMappings as $key => $value) {
            if ('string' === $value['type']) {
                $mapping[$document->getType()]['properties'][$value['fieldName']] = $this->getDefaultStringAnalyzer();
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
        return $index = new Index($indexName, new EsClient($this->container->getParameter(self::HOST_NAME)));
    }


    /**
     * @param $entity
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function getEntityMetadataFieldMappings($entity)
    {
        // TODO ADD CASHING
        $factory = new DisconnectedMetadataFactory($this->container->get('doctrine'));
        $metadataClass = $factory->getClassMetadata($entity)->getMetadata()[0];
        $baseMapping = $metadataClass->fieldMappings;

        foreach ($metadataClass->associationMappings as $fieldAssociation => $association) {
            $metadataAssociation = $factory->getClassMetadata($association['targetEntity'])->getMetadata()[0];
            $mappingAssociation = $metadataAssociation->fieldMappings;

            foreach ($mappingAssociation as $keyMapping => $mapping) {
                $mapping['fieldName'] = $fieldAssociation.'.'.$mapping['fieldName'];
                $baseMapping[$mapping['fieldName']] = $mapping ;
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
}

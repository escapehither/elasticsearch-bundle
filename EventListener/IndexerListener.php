<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Index;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\EsClient;
use Doctrine\ORM\EntityManager;
use EscapeHither\SearchManagerBundle\Entity\IndexableEntityInterface;

/**
 * Index delete and update Es Document.
 * Class IndexerListener
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class IndexerListener
{
    const INDEX_NAME = 'index_name';
    const FIELD_NAME = 'fieldName';

    /**
     *
     * @var EntityManager
     */
    private $em;

    /**
     * Es indexes configuration
     *
     * @var array
     */
    private $indexes;
    /**
     * Es host configuration.
     *
     * @var string.
     */
    private $host;

    /**
     * Index Listener Constructor.
     *
     * @param array         $indexes     The indexes
     * @param string        $host        Es host.
     * @param EntityManager $em Doctrine registry.
     */
    public function __construct($indexes, $host, EntityManager $em)
    {
        $this->indexes = $indexes;
        $this->host = $host;
        $this->em = $em;
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
     * {@inheritDoc}
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $class = get_class($entity);

        if ($this->indexHasParameter($class) && $entity instanceof IndexableEntityInterface) {
            $entity->trackMe();
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
     * Index a document.
     *
     * @param string $class
     * @param mixed  $object
     */
    protected function indexDocument($class, $object)
    {
        $parameter = $this->indexes[$class];
        $documentHandler = new DocumentHandler($object, $parameter);
        $document = $documentHandler->CreateDocument();

        $fieldMappings = $this->getEntityMetadataFieldMappings($class);

        foreach ($fieldMappings as $key => $value) {
            if ('string' === $value['type']) {
                $mapping[$document->getType()]['properties'][$key] = $this->getDefaultStringAnalyzer();
            }
        }

        $document->setMapping($mapping);
        $document->setId($object->getId());
        $this->getIndex($parameter[self::INDEX_NAME])->indexDocument($document);
    }

    /**
     * Get index.
     *
     * @param string $indexName
     *
     * @return Index
     */
    protected function getIndex($indexName)
    {
        return $index = new Index($indexName, new EsClient($this->host));
    }

    /**
     * Get entity metadata field mappings
     *
     * @param mixed $entity
     *
     * @return array
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function getEntityMetadataFieldMappings($entity)
    {
        // TODO ADD CASHING
        $metadataClass = $this->em->getClassMetadata($entity);
        $baseMapping = $metadataClass->fieldMappings;

        foreach ($metadataClass->associationMappings as $fieldAssociation => $association) {
            $metadataAssociation = $this->em->getClassMetadata($association['targetEntity']);
            $mappingAssociation = $metadataAssociation->fieldMappings;

            foreach ($mappingAssociation as $keyMapping => $mapping) {
                $name = $fieldAssociation.'.'.$mapping[self::FIELD_NAME];
                $baseMapping[$name] = $mapping ;
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

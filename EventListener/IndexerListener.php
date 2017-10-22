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
use Symfony\Component\Yaml\Yaml;


/**
 * Index delete and update Es Document.
 * Class IndexerListener
 * @package EscapeHither\SearchManagerBundle\EventListener
 */
class IndexerListener
{
    private $container;
    const INDEX_NAME = 'index_name';


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
        $mapping[$document->getType()] = [];
        foreach ($fieldMappings as $key => $value) {
            if($value['type']=='string'){
                $mapping[$document->getType()]['properties'][$value['fieldName']]=$this->getDefaultStringAnalyzer();
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
        return $index = new Index($indexName, new EsClient());
    }


    /**
     * @param $entity
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function getEntityMetadataFieldMappings($entity)
    {
        $factory = new DisconnectedMetadataFactory($this->container->get('doctrine'));
        $metadataClass = $factory->getClassMetadata($entity)->getMetadata()[0];
        return $metadataClass->fieldMappings;
    }

    protected function getDefaultStringAnalyzer()
    {
        return $default = [
            'type' => 'string',
            'analyzer' => 'standard',
            'fields'=>[
            'asciifolding' => [
                'type' => 'string',
                'analyzer' => 'folding_analyzer'
            ],
            'exact_value' => [
                'type' => 'string',
                'index' => 'not_analyzed'
            ]
        ]];

    }


}
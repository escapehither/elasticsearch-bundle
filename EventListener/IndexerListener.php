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
use EscapeHither\SearchManagerBundle\Utils\EsIndexer;
use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
use EscapeHither\SearchManagerBundle\Utils\Index;
use EscapeHither\SearchManagerBundle\Utils\EsClient;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Index delete and update Es Document.
 * Class IndexerListener
 * @package EscapeHither\SearchManagerBundle\EventListener
 */
class IndexerListener {
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
        $object= $args->getEntity();
        $class = get_class($object);
        if($this->container->hasParameter($class)){
            $this->indexDocument($class, $object);
        }

    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $object= $args->getEntity();
        $class = get_class($object);
        if($this->container->hasParameter($class)){
            $this->indexDocument($class, $object);
        }

    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $object= $args->getEntity();
        $class = get_class($object);
        if($this->container->hasParameter($class)){
        $parameter = $this->container->getParameter($class);
        EsIndexer::DeleteDocument($parameter[self::INDEX_NAME],$parameter['type'],$object->getId());
        }

    }

    /**
     * @param $class
     * @param $object
     */
    protected function indexDocument($class, $object) {
        $parameter = $this->container->getParameter($class);
        $documentHandler = new DocumentHandler($object, $parameter);
        $document = $documentHandler->CreateDocument();
        $document->setId($object->getId());
        $this->getIndex($parameter[self::INDEX_NAME])->indexDocument($document);
    }

    /**
     * @param $indexName
     * @return Index
     */
    protected function getIndex($indexName){
        return $index = new Index($indexName,new EsClient());
    }


}
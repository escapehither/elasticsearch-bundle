<?php
/**
 * This file is part of the Genia package.
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
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
class IndexerListener {
    private $container;
    /**
     *
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $object= $args->getEntity();
        $class = get_class($object);
        if($this->container->hasParameter($class)){
            $this->indexDocument($class, $object);
        }

    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $object= $args->getEntity();
        $class = get_class($object);
        if($this->container->hasParameter($class)){
            $this->indexDocument($class, $object);
        }

    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $object= $args->getEntity();
        $class = get_class($object);
        if($this->container->hasParameter($class)){
        $parameter = $this->container->getParameter($class);
        EsIndexer::DeleteDocument($parameter['index_name'],$parameter['type'],$object->getId());
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
        EsIndexer::IndexDocument($parameter['index_name'],$parameter['type'],$object->getId(),$document);
    }


}
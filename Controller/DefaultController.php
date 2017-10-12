<?php

namespace EscapeHither\SearchManagerBundle\Controller;
use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
class DefaultController extends Controller
{
    public function indexAction()
    {
        $repository = $this->get('doctrine.orm.default_entity_manager')->getRepository('OpenMarketPlaceProductManagerBundle:Product');
        $object = $repository->find(429);

        $config = $this->getParameter('OpenMarketPlace\ProductManagerBundle\Entity\Product');
        $documentHandler = new DocumentHandler($object,$config);
        $document = $documentHandler->CreateDocument();


        die('ok');
        return $this->render('EscapeHitherSearchManagerBundle:Default:index.html.twig');
    }

}

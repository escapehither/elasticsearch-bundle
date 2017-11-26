<?php

namespace EscapeHither\SearchManagerBundle\Controller;
use EscapeHither\SearchManagerBundle\Utils\DocumentHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
class DefaultController extends Controller
{
    const REQUEST_PARAMETER_HANDLER = 'escapehither.search_request_parameter_handler';
    public function indexAction(Request $request)
    {

        /*$repository = $this->get('doctrine.orm.default_entity_manager')->getRepository('OpenMarketPlaceProductManagerBundle:Product');
        $object = $repository->find(429);

        $config = $this->getParameter('OpenMarketPlace\ProductManagerBundle\Entity\Product');
        $documentHandler = new DocumentHandler($object,$config);
        $document = $documentHandler->CreateDocument();


        die('ok');
        return $this->render('EscapeHitherSearchManagerBundle:Default:index.html.twig');*/
        $string = $request->get('search');
        $requestParameterHandler = $this->getRequestParameterHandler();
        $format = $requestParameterHandler->getFormat();

        dump($requestParameterHandler);
        die();
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('OpenMarketPlaceProductManagerBundle:Product')->search($string);
        return $this->render($requestParameterHandler->getThemePath(), array(
            'products' => $results,
            'string'   => $string
        ));
    }
    public function searchAction(Request $request)
    {

        $requestParameterHandler = $this->getRequestParameterHandler();
        $format = $requestParameterHandler->getFormat();
        // ADD Check if the user have authorisation before proceeding from the request.
        $searchRequestHandler = $this->get('escapehither.search_request_handler');
        $resources = $searchRequestHandler->search();

        if ($format == 'html') {
            return $this->render($requestParameterHandler->getThemePath(),$resources );
        }

        $serializer = $this->getSerializer();
        $jsonContent = $serializer->serialize($resources, 'json');
        $response = new Response($jsonContent, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
        return $this->render('EscapeHitherSearchManagerBundle:Default:index.html.twig');
        $string = $request->get('search');
        $requestParameterHandler = $this->getRequestParameterHandler();
        $format = $requestParameterHandler->getFormat();
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('OpenMarketPlaceProductManagerBundle:Product')->search($string);
        return $this->render('OpenMarketPlaceSearchManagerBundle:Default:index.html.twig', array(
            'products' => $results,
            'string'   => $string
        ));
    }
    /**
     * @return \EscapeHither\CrudManagerBundle\Services\RequestParameterHandler
     */
    protected function getRequestParameterHandler() {
        $requestParameterHandler = $this->get(self::REQUEST_PARAMETER_HANDLER);
        $requestParameterHandler->build();
        return $requestParameterHandler;
    }
    public function getSerializer() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        // This line help avoid circular reference.
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizers = array($normalizer);
        return new Serializer($normalizers, $encoders);

    }



}

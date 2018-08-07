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

/**
 * Default search controller.
 */
class DefaultController extends Controller
{
    const REQUEST_PARAMETER_HANDLER = 'escapehither.search_request_parameter_handler';

    /**
     * Search action.
     *
     * @param Request $request
     * @return void
     */
    public function searchAction(Request $request)
    {

        $requestParameterHandler = $this->getRequestParameterHandler();
        $format = $requestParameterHandler->getFormat();
        // ADD Check if the user have authorisation before proceeding from the request.
        $searchRequestHandler = $this->get('escapehither.search_request_handler');
        $resources = $searchRequestHandler->search();

        if ($format == 'html') {
            return $this->render($requestParameterHandler->getThemePath(), $resources);
        }
        //TODO add other format.
    }

    /**
     * @return \EscapeHither\CrudManagerBundle\Services\RequestParameterHandler
     */
    protected function getRequestParameterHandler()
    {
        $requestParameterHandler = $this->get(self::REQUEST_PARAMETER_HANDLER);
        $requestParameterHandler->build();

        return $requestParameterHandler;
    }
}

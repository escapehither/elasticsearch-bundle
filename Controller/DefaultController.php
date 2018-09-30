<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class DefaultController extends Controller
{
    const REQUEST_PARAMETER_HANDLER = 'escapehither.search_request_parameter_handler';

    /**
     * Search action.
     *
     * @param Request $request
     *
     * @return void
     */
    public function searchAction(Request $request)
    {
        $requestParameterHandler = $this->getRequestParameterHandler();
        $format = $requestParameterHandler->getFormat();
        // ADD Check if the user have authorisation before proceeding from the request.
        $searchRequestHandler = $this->get('escapehither.search_request_handler');
        $resources = $searchRequestHandler->search();

        if ('html' === $format) {
            return $this->render($requestParameterHandler->getThemePath(), $resources);
        }
        //TODO add other format.
    }

    /**
     * Get the request parameter handler.
     *
     * @return \EscapeHither\CrudManagerBundle\Services\RequestParameterHandler
     */
    protected function getRequestParameterHandler()
    {
        $requestParameterHandler = $this->get(self::REQUEST_PARAMETER_HANDLER);
        $requestParameterHandler->build();

        return $requestParameterHandler;
    }
}

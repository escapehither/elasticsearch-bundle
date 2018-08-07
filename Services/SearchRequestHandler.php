<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 20/11/16
 * Time: 21:04
 */

namespace EscapeHither\SearchManagerBundle\Services;

use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\SearchRequest;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\Index;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\EsClient;
use EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp\EasyElasticSearchAdapter;

/**
 * The search request handler.
 */
class SearchRequestHandler
{
    const HOST_NAME = 'escape_hither_search_manager.host';

    /**
     * @var RequestParameterHandler
     */
    protected $requestParameterHandler;

    /**
     * @var EntityManager
     */
    protected $em;
    private $request;
    private $container;
    private $links = [];


    /**
     * The request parameter handler constructor.
     *
     * @param RequestParameterHandler $requestParameterHandler The reuqest parameter handler.
     */
    public function __construct(RequestParameterHandler $requestParameterHandler)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->request = $this->requestParameterHandler->getRequest();
        $this->container = $this->requestParameterHandler->container;
    }

    /**
     * Process the request.
     *
     * @return void
     */
    public function process()
    {
        $format = $this->requestParameterHandler->getFormat();
        $searchRequest = new SearchRequest();
        $index = new Index($this->requestParameterHandler->getIndexName(), new EsClient($this->container->getParameter(self::HOST_NAME)));
        $results = $index->search($searchRequest);
    }

    /**
     * Add links.
     *
     * @param [type] $ref
     * @param [type] $url
     * @return void
     */
    public function addLink($ref, $url)
    {
        $this->links[$ref] = $url;
    }

    /**
     * Search the resource.
     *
     * @return void
     */
    public function search()
    {
        $page = 1;
        if (!empty($this->request->query->get('page'))) {
            $page = $this->request->query->get('page');
        }

        // Item per page to display.

        $format = $this->requestParameterHandler->getFormat();
        $searchRequest = new SearchRequest();

        if ($this->requestParameterHandler->getString()) {
            $searchRequest->setString($this->requestParameterHandler->getString());
        }

        $index = new Index($this->requestParameterHandler->getIndexName(), new EsClient($this->container->getParameter(self::HOST_NAME)));
        $adapter = new EasyElasticSearchAdapter($searchRequest, $index);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setCurrentPage($page);

        $pagerFanta->setMaxPerPage($this->requestParameterHandler->getPaginationSize());
        $results = $pagerFanta->getCurrentPageResults();

        if ('html' === $format) {
            return  ['data' => $pagerFanta,
                    'string' => $this->requestParameterHandler->getString(),
                ];
        }

        $data['data'] = $results['hits']['hits'];
        $data['pagination'] = [
            'total' => $pagerFanta->count(),
            'count' => count($data['data']),
            'current_page' => $pagerFanta->getCurrentPage(),
            'per_page' => $pagerFanta->getMaxPerPage(),
            'total_pages' => $pagerFanta->getNbPages(),
            'links' => $this->getLinks($pagerFanta, $this->request),
        ];

        return $data;
    }

    /**
     * @param $repositoryMethod
     * @param $repositoryArguments
     * @param $repository
     * @return mixed
     */
    protected function getResourcesFromMethod($repositoryMethod, $repositoryArguments, $repository)
    {
        if (null !== $repositoryArguments) {
            $callable = [$repository, $repositoryMethod];

            return call_user_func_array($callable, $repositoryArguments);
        } elseif (null === $repositoryArguments) {
            $callable = [$repository, $repositoryMethod];

            return call_user_func($callable);
        }

        return [];
    }

    /**
     * Get the pagination links.
     *
     * @param Pagerfanta $pagerFanta
     * @return void
     */
    private function getLinks(Pagerfanta $pagerFanta)
    {
        $route = $this->request->attributes->get('_route');
        // make sure we read the route parameters from the passed option array
        $defaultRouteParams = array_merge($this->request->query->all(), $this->request->attributes->get('_route_params', array()));
        $createLinkUrl = function ($targetPage) use ($route, $defaultRouteParams) {
            $router = $this->container->get('router');

            return $router->generate($route, array_merge(
                $defaultRouteParams,
                array('page' => $targetPage)
            ));
        };

        $this->addLink('self', $createLinkUrl($pagerFanta->getCurrentPage()));
        $this->addLink('first', $createLinkUrl(1));
        $this->addLink('last', $createLinkUrl($pagerFanta->getNbPages()));

        if ($pagerFanta->hasNextPage()) {
            $this->addLink('next', $createLinkUrl($pagerFanta->getNextPage()));
        }
        if ($pagerFanta->hasPreviousPage()) {
            $this->addLink('prev', $createLinkUrl($pagerFanta->getPreviousPage()));
        }

        return $this->links;
    }
}

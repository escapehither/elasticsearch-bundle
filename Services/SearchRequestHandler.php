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
    const HOST_NAME = 'escape_hither.search_manager.host';
    const INDEXES = 'escape_hither.search_manager.indexes';


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
    private $indexName;
    private $host;
    private $tags;

    /**
     * The request parameter handler constructor.
     *
     * @param RequestParameterHandler $requestParameterHandler The reuqest parameter handler.
     * @param string                  $host
     * @param array                   $indexes
     */
    public function __construct(RequestParameterHandler $requestParameterHandler, $host, $indexes)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->host = $host;
        $this->requestParameterHandler->build();
        $this->request = $this->requestParameterHandler->getRequest();
        $this->container = $this->requestParameterHandler->container;
        $this->indexConfig = $indexes[$this->requestParameterHandler->getIndexEntity()];
        $this->indexName = $this->indexConfig['index_name'];
        $this->resourceType = $this->indexConfig['type'];
        $this->tags = $this->indexConfig['facets']['tags_relation'];
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
        $index = new Index($this->indexName, new EsClient($this->host));
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
        $searchRequest = new SearchRequest();
        $searchRequest->setType($this->resourceType);
        $facetProvider = new EsFacetProvider($this->indexConfig, $this->host);
        $filters = [];

        $page = 1;
        if (!empty($this->request->query->get('page'))) {
            $page = $this->request->query->get('page');
        }
        $parameter = $this->request->query->all();

        if (!empty($parameter['filters'])) {
            $facetDispatched = $facetProvider->dispatchFilter($parameter['filters']);
            empty($facetDispatched) ? :$searchRequest->addFilter('terms', $facetDispatched);
            empty($parameter['filters']['date']) ? :$searchRequest->addFilter('term', $parameter['filters']['date']);
            empty($parameter['filters']['range-date']) ? :$searchRequest->addFilter('range', $parameter['filters']['range-date']);
            empty($parameter['filters']['range-price']) ? :$searchRequest->addFilter('range', $parameter['filters']['range-price']);
            $filters = $parameter['filters'];
        }

        $aggs = [];

        foreach ($this->indexConfig['facets']['tags_relation'] as $keyRelation => $relation) {
            $aggs = [
                $keyRelation => [
                    'name' => $keyRelation,
                    'size' => 1000, //TODO must be greater than Zero.
                ],
            ];
        }

        empty($aggs) ? :$searchRequest->addAggs($aggs);
        $searchRequest->addAggs($aggs);

        if ($this->requestParameterHandler->getString()) {
            $searchRequest->setString($this->requestParameterHandler->getString());
        }

        $index = new Index($this->indexName, new EsClient($this->host));
        $adapter = new EasyElasticSearchAdapter($searchRequest, $index);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setCurrentPage($page);

        $pagerFanta->setMaxPerPage($this->requestParameterHandler->getPaginationSize());
        $results = $pagerFanta->getCurrentPageResults();
        $facets = $facetProvider->getFacets($results);
        $facetTags = $facetProvider->getFacetsTag();

        if ('html' === $this->requestParameterHandler->getFormat()) {
            return  ['data' => $pagerFanta,
                    'string' => $this->requestParameterHandler->getString(),
                    'facets' => $facets,
                    'facetTags' => $facetTags,
                    'filters' => $filters,
                    'sort' => 'default',
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

<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class SearchRequestHandler
{
    const HOST_NAME = 'escape_hither.search_manager.host';
    const INDEXES = 'escape_hither.search_manager.indexes';
    const FILTERS = 'filters';
    const DATA = 'data';
    const FACETS = 'facets';
    const FACET_TAGS = 'facetTags';
    const RANGE_DATE = 'range-date';
    const RANGE_PRICE = 'range-price';
    const RANGE = 'range';

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
    private $filters = [];
    private $searchRequest;

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
        // TODO remove give what we need directly
        $this->container = $this->requestParameterHandler->container;
        $this->indexConfig = $indexes[$this->requestParameterHandler->getIndexEntity()];
        $this->indexName = $this->indexConfig['index_name'];
        $this->resourceType = $this->indexConfig['type'];
        $this->tags = $this->indexConfig[self::FACETS]['tags_relation'];
        $this->searchRequest = new SearchRequest();
    }

    /**
     * Process the request.
     *
     * @return void
     */
    public function process()
    {
        $format = $this->requestParameterHandler->getFormat();
        $index = new Index($this->indexName, new EsClient($this->host));
        $results = $index->search($this->searchRequest);
    }

    /**
     * Add links.
     *
     * @param string $ref
     * @param string $url
     *
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
        $this->searchRequest->setType($this->resourceType);
        $facetProvider = new EsFacetProvider($this->indexConfig, $this->host);

        $this->initFilters();
        $this->addAggregate();

        if ($this->requestParameterHandler->getString()) {
            $this->searchRequest->setString($this->requestParameterHandler->getString());
        }

        return $this->buildView();
    }

    /**
     * Get the pagination links.
     *
     * @param Pagerfanta $pagerFanta
     *
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

    /**
     * Init request filter.
     *
     * @return void
     */
    private function initFilters()
    {
        $parameter = $this->requestParameterHandler->getRequestParameter();

        if (!empty($parameter[self::FILTERS])) {
            $facetDispatched = $facetProvider->dispatchFilter($parameter[self::FILTERS]);
            empty($facetDispatched) ? :$this->searchRequest->addFilter('terms', $facetDispatched);
            empty($parameter[self::FILTERS]['date']) ? :$this->searchRequest->addFilter('term', $parameter[self::FILTERS]['date']);
            empty($parameter[self::FILTERS][self::RANGE_DATE]) ? :$this->searchRequest->addFilter(self::RANGE, $parameter[self::FILTERS][self::RANGE_DATE]);
            empty($parameter[self::FILTERS][self::RANGE_PRICE]) ? :$this->searchRequest->addFilter(self::RANGE, $parameter[self::FILTERS][self::RANGE_PRICE]);
            $this->filters = $parameter[self::FILTERS];
        }
    }

    /**
     * Add aggregate
     *
     * @return void
     */
    private function addAggregate()
    {
        $aggs = [];

        foreach ($this->indexConfig[self::FACETS]['tags_relation'] as $keyRelation => $relation) {
            $aggs = [
                $keyRelation => [
                    'name' => $keyRelation,
                    'size' => 1000, //TODO must be greater than Zero.
                ],
            ];
        }
        // TODo check witch one to leave
        empty($aggs) ? :$this->searchRequest->addAggs($aggs);
        $this->searchRequest->addAggs($aggs);
    }
    /**
     * Build research view;
     *
     * @return void
     */
    private function buildView()
    {
        $index = new Index($this->indexName, new EsClient($this->host));
        $adapter = new EasyElasticSearchAdapter($this->searchRequest, $index);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setCurrentPage($this->requestParameterHandler->getCurrentPage());
        $pagerFanta->setMaxPerPage($this->requestParameterHandler->getPaginationSize());
        $results = $pagerFanta->getCurrentPageResults();
        $facets = $facetProvider->getFacets($results);
        $facetTags = $facetProvider->getFacetsTag();

        if ('html' === $this->requestParameterHandler->getFormat()) {
            return  [self::DATA => $pagerFanta,
                    'string' => $this->requestParameterHandler->getString(),
                    self::FACETS => $facets,
                    self::FACET_TAGS => $facetTags,
                    self::FILTERS => $this->filters,
                    'sort' => 'default',
                   ];
        }

        $data[self::DATA] = $results['hits']['hits'];
        $data['pagination'] = [
            'total' => $pagerFanta->count(),
            'count' => count($data[self::DATA]),
            'current_page' => $pagerFanta->getCurrentPage(),
            'per_page' => $pagerFanta->getMaxPerPage(),
            'total_pages' => $pagerFanta->getNbPages(),
            'links' => $this->getLinks($pagerFanta, $this->request),
        ];

        return $data;
    }
}

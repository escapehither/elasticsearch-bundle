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
class SearchRequestHandler {

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
    private $_links = [];


    function __construct(RequestParameterHandler $requestParameterHandler, EntityManager $em)
    {
        $this->requestParameterHandler = $requestParameterHandler;
        $this->requestParameterHandler->build();
        $this->em = $em;
        $this->request = $this->requestParameterHandler->getRequest();
        $this->container = $this->requestParameterHandler->container;

    }

    public function process(){
        $format=$this->requestParameterHandler->getFormat();
        dump($this->requestParameterHandler);
        $searchRequest = new SearchRequest();
        dump($searchRequest->generateRequest());

        $index = new Index($this->requestParameterHandler->getIndexName(),new EsClient());
        dump($index->search($searchRequest));
        die();
        $repository = $this->em->getRepository($this->requestParameterHandler->getRepositoryClass());
        $repositoryArguments = $this->requestParameterHandler->getRepositoryArguments();
        $repositoryMethod = $this->requestParameterHandler->getRepositoryMethod();
        if(NULL != $repositoryMethod){
            return $this->getResourcesFromMethod($repositoryMethod, $repositoryArguments, $repository);
        }

        // TODO CLEAN UP  AND CHECK IF THE REQUEST NEED PAGINATION.
        $qb = $repository->createQueryBuilder('resource');
        $adapter = new DoctrineORMAdapter($qb);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage(5);
        $page = 1;
        if(!empty($this->request->query->get('page'))){
            $page = $this->request->query->get('page');
        }
        $pagerFanta->setCurrentPage($page);

        $result = $pagerFanta->getCurrentPageResults();
        if($format=='html'){
            return $pagerFanta;
        }
        else{
            $list['data'] = $result->getArrayCopy();
            $list['pagination'] =[
              'total'=>$pagerFanta->count(),
              'count'=>$pagerFanta->getCurrentPageResults()->count(),
              'current_page'=>$pagerFanta->getCurrentPage(),
              'per_page'=>$pagerFanta->getMaxPerPage(),
              'total_pages'=>$pagerFanta->getNbPages(),
               'links'=>$this->getLinks($pagerFanta),

            ];

            return $list;
        }

        // TODO Check if the pagination is not needed and if is limited
        // TODO add criteria and sorting.

    }

    /**
     * @param $repositoryMethod
     * @param $repositoryArguments
     * @param $repository
     * @return mixed
     */
    protected function getResourcesFromMethod($repositoryMethod, $repositoryArguments, $repository) {

        if ($repositoryArguments != NULL) {
            $callable = [$repository, $repositoryMethod];
            return call_user_func_array($callable, $repositoryArguments);
        }
        elseif (  $repositoryArguments == NULL ) {
            $callable = [$repository, $repositoryMethod];
            return call_user_func($callable);

        }
       return [];
    }

    // TODO cleaning
    private function getLinks(Pagerfanta $pagerFanta){

        $route = $this->request->attributes->get('_route');
        // make sure we read the route parameters from the passed option array
        $defaultRouteParams = array_merge($this->request->query->all(), $this->request->attributes->get('_route_params', array()));
        $createLinkUrl = function($targetPage) use ($route, $defaultRouteParams) {
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
        return $this->_links;

    }
    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }
    public function searchAction(Request $request){
        $page = 1;
        if(!empty($request->query->get('page'))){
            $page = $request->query->get('page');
        }
        $index = 'catalogue';
        $type = 'iso19139';

        $text = '*';
        if ($request) {
            $parameter = $request->query->all();
            if (isset($parameter['search_text'])) {
                if (empty($parameter['search_text'])) {
                    $text = '*';
                }
                else {
                    $text = $parameter['search_text'];
                }

            }

        }
        $customParameterFilter = [];
        $customParameterFilter['index'] = $index;
        // Item per page to display.
        $itemPerPage = 30;

        //$customParameterFilter['from'] = $from;
        //$customParameterFilter['size'] = $itemPerPage;
        $customParameterFilter['type'] = $type;
        $customParameterFilter['body']['query']['filtered']['query']['query_string']['query'] = $text;


        $adapter = new EasyElasticSearchAdapter($customParameterFilter);
        $pagerFanta = new Pagerfanta($adapter);

        $pagerFanta->setCurrentPage($page);
        $pagerFanta->setMaxPerPage($itemPerPage);

        $result = $pagerFanta->getCurrentPageResults();
        $list['data'] = $result['hits']['hits'];
        $list['pagination'] =[
            'total'=>$pagerFanta->count(),
            'count'=>count($result),
            'current_page'=>$pagerFanta->getCurrentPage(),
            'per_page'=>$pagerFanta->getMaxPerPage(),
            'total_pages'=>$pagerFanta->getNbPages(),
            'links'=>$this->getLinks($pagerFanta,$request),

        ];

        $serializer = $this->getSerializer();
        $jsonContent = $serializer->serialize($list, 'json');
        $response = new Response($jsonContent, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;


    }
//    private function getLinks(Pagerfanta $pagerFanta, Request $request){
//
//        $route = $request->attributes->get('_route');
//        // make sure we read the route parameters from the passed option array
//        $defaultRouteParams = array_merge($request->query->all(), $request->attributes->get('_route_params', array()));
//        $createLinkUrl = function($targetPage) use ($route, $defaultRouteParams) {
//            $router = $this->container->get('router');
//            return $router->generate($route, array_merge(
//                $defaultRouteParams,
//                array('page' => $targetPage)
//            ));
//        };
//
//        $this->addLink('self', $createLinkUrl($pagerFanta->getCurrentPage()));
//        $this->addLink('first', $createLinkUrl(1));
//        $this->addLink('last', $createLinkUrl($pagerFanta->getNbPages()));
//
//        if ($pagerFanta->hasNextPage()) {
//            $this->addLink('next', $createLinkUrl($pagerFanta->getNextPage()));
//        }
//        if ($pagerFanta->hasPreviousPage()) {
//            $this->addLink('prev', $createLinkUrl($pagerFanta->getPreviousPage()));
//        }
//        return $this->_links;
//
//    }
//    public function addLink($ref, $url)
//    {
//        $this->_links[$ref] = $url;
//    }

}

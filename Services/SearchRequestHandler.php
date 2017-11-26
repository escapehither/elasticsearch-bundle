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
        $results = $index->search($searchRequest);
        dump($results);
        die();


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
    public function search(){

        $page = 1;
        if(!empty($this->request->query->get('page'))){
            $page = $this->request->query->get('page');
        }

        // Item per page to display.

        $format=$this->requestParameterHandler->getFormat();
        $searchRequest = new SearchRequest();

        if($this->requestParameterHandler->getString()){
            $searchRequest->setString($this->requestParameterHandler->getString());
        }

        $index = new Index($this->requestParameterHandler->getIndexName(),new EsClient());
        $adapter = new EasyElasticSearchAdapter($searchRequest,$index );
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setCurrentPage($page);

        $pagerFanta->setMaxPerPage($this->requestParameterHandler->getPaginationSize());



        $results = $pagerFanta->getCurrentPageResults();
        if ($format == 'html') {
           return  ['data' => $pagerFanta,
                    'string'=>$this->requestParameterHandler->getString()
           ];



        }
        $data['data'] = $results['hits']['hits'];
        $data['pagination'] =[
            'total'=>$pagerFanta->count(),
            'count'=>count($data['data']),
            'current_page'=>$pagerFanta->getCurrentPage(),
            'per_page'=>$pagerFanta->getMaxPerPage(),
            'total_pages'=>$pagerFanta->getNbPages(),
            'links'=>$this->getLinks($pagerFanta,$this->request),];

        return $data;


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

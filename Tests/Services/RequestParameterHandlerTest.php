<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Tests\Services;

use EscapeHither\SearchManagerBundle\Services\RequestParameterHandler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Request parameter handler test.
 */
final class RequestParameterHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $query = [];
    protected $container;

    const REQUEST_ATTRIBUTES = [
        "_controller" => "EscapeHither\SearchManagerBundle\Controller\DefaultController::searchAction",
        "template" => "OpenMarketPlaceSearchManagerBundle:Default:index.html.twig",
        "index" => [
            "entity" => "OpenMarketPlace\ProductManagerBundle\Entity\Product",
            "type" => "product",
        ],
            "pagination" => [
                "size" => 21,
            ],
            "_route" => "genia_search",
            "_route_params" =>  [
            "template" => "OpenMarketPlaceSearchManagerBundle:Default:index.html.twig",
            "index" =>  [
                "entity" => "OpenMarketPlace\ProductManagerBundle\Entity\Product",
                "type" => "product",
            ],
            "pagination" => [
                "size" => 21,
            ],
        ],
    ];

    public function setUp()
    {
        $this->query = [];
        $this->container = $this->getMockBuilder(ContainerInterface::class)->getMock();
    }
    /**
     * test the service builder.
     *
     * @return void
     */
    public function testBuild()
    {
        $attributes = self::REQUEST_ATTRIBUTES;
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('searchAction', $requestParameterHandler->getActionName());
        $this->assertNotEmpty($requestParameterHandler->getAttributes());
    }

    /**
     * Test get action name
     *
     * @return void
     */
    public function testGetActionName()
    {
        $action_list = [
            'indexAction',
            'apiIndexAction',
            'editAction',
            'apiEditAction',
            'showAction',
            'apiShowAction',
            'newAction',
            'apiNewAction',
            'deleteAction',
            'apiDeleteAction',

        ];
        foreach ($action_list as $value) {
            $attributes = [
                "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::".$value,
            ];
            $requestParameterHandler = $this->buildRequest($attributes);
            $this->assertEquals($value, $requestParameterHandler->getActionName());
        }
    }

    /**
     * Test get route name
     *
     * @return void
     */
    public function testGetRouteName()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_index',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('product_index', $requestParameterHandler->getRouteName());
    }

    /**
     * @return string
     */
    public function testGetIndexRoute()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_show',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('product_index', $requestParameterHandler->getIndexRoute());
    }

    /**
     * Get The type of request.
     *
     * @return string
     */
    public function testGetType()
    {
        $attributes = self::REQUEST_ATTRIBUTES;
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('product', $requestParameterHandler->getType());
    }

    /**
     * test get entity
     *
     * @return void
     */
    public function testGetEntity()
    {
        $attributes = self::REQUEST_ATTRIBUTES;
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('OpenMarketPlace\ProductManagerBundle\Entity\Product', $requestParameterHandler->getIndexEntity());
    }
    /**
     * Get the theme path.
     */
    public function testGetThemePath()
    {
        $attributes = self::REQUEST_ATTRIBUTES;
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('OpenMarketPlaceSearchManagerBundle:Default:index.html.twig', $requestParameterHandler->getThemePath());    
    }

    public function testGetFormat()
    {
        $attributes = self::REQUEST_ATTRIBUTES;
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('html', $requestParameterHandler->getFormat());
    }
    /**
     * Test get pagination size
     *
     * @return void
     */
    public function testGetPaginationSize()
    {
        // Default pagination
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::apiIndexAction",
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals(10, $requestParameterHandler->getPaginationSize());
        
        // Change pagination size.
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::apiIndexAction",
            "pagination"  => ["size"=>20]
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals(20, $requestParameterHandler->getPaginationSize());
    }

    /**
     * Get the resource view name.
     */
    public function testGetResourceViewName()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_index',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('products', $requestParameterHandler->getResourceViewName());
    }

    /**
     * Get the resource config name.
     *
     * @return string
     */
    public function testGetResourceConfigName()
    {

        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_index',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals(null, $requestParameterHandler->getResourceConfigName());

        //Get the resource config name only for a search action.
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::searchAction",
            '_route'=>'product_index',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('resource-product', $requestParameterHandler->getResourceConfigName());
    }

     /**
     * Get the resource name.
     *
     * @return mixed
     */
    public function testGetResourceName()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_show',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('product', $requestParameterHandler->getResourceName());
    }

    /**
     * Get the bundle name.
     *
     * @return mixed
     */
    public function testGetBundleName()
    { //TODO CHECK if NEEDED
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_show',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('OpenMarketPlace', $requestParameterHandler->getBundleName());
    }

    /**
     * Get the request string.
     * 
     * @return null|string
     */
    public function testGetString()
    {
        $attributes = self::REQUEST_ATTRIBUTES;
        $this->query['string'] = 'toto';
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('toto', $requestParameterHandler->getString());
        
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function testGetCurrentPage()
    {
        $attributes = self::REQUEST_ATTRIBUTES;
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('1', $requestParameterHandler->getCurrentPage());

        $this->query['page'] = '20';
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('20', $requestParameterHandler->getCurrentPage());
    }

    /**
     * Get request parameter.
     *
     * @return array
     */
    public function getRequestParameter()
    {
        $parameter = [
            'page'=> 20,
            'string' => 'coco',
            'range-date' => [
                'from' => '10-10-2019',
                'to' => '10-20-2019',
            ]
        ];

        $this->query['page'] = '20';
        $this->query['string'] = 'coco';
        $this->query['range-date']['from'] = '10-10-2019';
        $this->query['range-date']['to'] = '10-20-2019';

        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals($parameter, $requestParameterHandler->getRequestParameter());
    }
     /**
     * Get the ressource class.
     *
     * @return string
     */
    public function testGetResourceClass()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_show',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('OpenMarketPlace\ProductManagerBundle\Entity\Product', $requestParameterHandler->getResourceClass());

        $attributes = [
            "_controller" => "OpenMarketPlace\Other\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_show',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('OpenMarketPlace\Other\ProductManagerBundle\Entity\Product', $requestParameterHandler->getResourceClass());
    }

    protected function buildRequest($attributes, $container = null)
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $request->initialize($this->query, [], $attributes);
        $requestStack->push($request);
        $requestParameterHandler= new RequestParameterHandler($requestStack, $this->container);
        $requestParameterHandler->build();

        return $requestParameterHandler;
    }
}

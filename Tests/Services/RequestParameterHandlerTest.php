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
class RequestParameterHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        //TODO
    }
    public function testBuild()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('indexAction', $requestParameterHandler->getActionName());
        $this->assertNotEmpty($requestParameterHandler->getAttributes());
    }
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
    public function testGetRouteName()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_index',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('product_index', $requestParameterHandler->getRouteName());
    }
    public function testGetResourceClass()
    {
        //TODO
    }
    public function testGenerateDeleteRoute()
    {
        //TODO
    }
    public function testGetFormat()
    {
        //TODO
    }
    /**
     * @return string
     */
    public function testGetRedirectionRoute()
    {
    }

    protected function buildRequest($attributes)
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $request->initialize([], [], $attributes);
        $requestStack->push($request);
        $requestParameterHandler= new RequestParameterHandler($requestStack, $container);
        $requestParameterHandler->build();

        return $requestParameterHandler;
    }
}

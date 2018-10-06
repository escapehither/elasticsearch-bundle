<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use EscapeHither\SearchManagerBundle\Utils\AbstractRequestParameterHandler;

/**
 * Search request Parameter Handler.
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class RequestParameterHandler extends AbstractRequestParameterHandler
{
    protected $bundleName;
    protected $request;
    protected $requestStack;
    protected $resourceName;
    protected $resourceServiceName;
    protected $resourceConfigName;
    protected $resourceViewName;
    protected $themePath;
    protected $redirectionRoute;
    protected $indexRoute;
    protected $indexConfig;
    protected $indexClass;
    protected $formConfig;
    protected $formClass;
    protected $format;
    protected $securityConfig;
    protected $routeName;
    protected $actionName;
    protected $paginationConfig;

    /**
     * THe request parameter Handler constructor.
     *
     * @param RequestStack $requestStack The request stack.
     * @param Container    $container    The conatainer.
     */
    public function __construct(RequestStack $requestStack, Container $container)
    {
        $this->requestStack = $requestStack;
        $this->container = $container;
    }

    /**
     * Handler build.
     *
     * @return void
     */
    public function build()
    {
        $this->request = $this->requestStack->getCurrentRequest();

        if ($this->request) {
            $this->format = $this->request->getRequestFormat();
        }

        $attributes = $this->getAttributes();

        if (!empty($attributes)) {
            $this->resourceName = $attributes['name'];
            $actionList = [
              'searchAction',
            ];

            if ("redirect" === $this->resourceName) {
                return;
            }
            if (in_array($attributes['action'], $actionList)) {
                // use when call resource configuration parameter.
                $this->resourceConfigName = 'resource-'.$attributes['nameConfig'];

                if ($this->container->hasParameter($this->resourceConfigName)) {
                    $parameters = $this->container->getParameter(
                        $this->resourceConfigName
                    );
                    $this->indexClass = $parameters['entity'];
                }
            }

            // use when call resource configuration parameter.
            $this->resourceServiceName = 'resource.'.$attributes['nameConfig'];
            // The name use for generating the view.
            $this->resourceViewName = $this->generateResourceViewName($attributes);
            // The where is template for the view.
            $this->themePath = $this->generateThemePath($attributes);
            // The bundle name.
            $this->bundleName = $attributes['bundle'];
            // The index root.
            $this->indexRoute = $attributes['nameConfig'].'_index';
            // Repository configuration.
            $this->indexConfig = $attributes['index'];

            if (isset($attributes['pagination'])) {
                $this->paginationConfig = $attributes['pagination'];
            }

            $this->formConfig = $attributes['form'];
            $this->securityConfig = $attributes['security'];
            $this->actionName = $attributes['action'];
            $this->routeName = $attributes['_route'];
        }
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return mixed
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return string
     */
    public function getIndexRoute()
    {
        return $this->indexRoute;
    }

    /**
     * Get the index name.
     *
     * @return string
     */
    public function getIndexEntity()
    {
        return $this->indexConfig['entity'];
    }

    /**
     * Get The type of request.
     *
     * @return string
     */
    public function getType()
    {
        return $this->indexConfig['type'];
    }

    /**
     * Get the pagination size.
     *
     * @return int/void
     */
    public function getPaginationSize()
    {
        $size = 10;
        if (isset($this->paginationConfig['size'])) {
            $size = $this->paginationConfig['size'];
        }

        return $size;
    }

    /**
     * Get the request format.
     *
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Get the repository class.
     *
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->indexClass;
    }

    /**
     * Get the theme path.
     *
     * @return string
     */
    public function getThemePath()
    {
        return $this->themePath;
    }

    /**
     * Get the resource view name.
     *
     * @return string
     */
    public function getResourceViewName()
    {
        return $this->resourceViewName;
    }

    /**
     * Get the resource config name.
     *
     * @return string
     */
    public function getResourceConfigName()
    {
        return $this->resourceConfigName;
    }

    /**
     * Get the resource name.
     *
     * @return mixed
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * Get the bundle name.
     *
     * @return mixed
     */
    public function getBundleName()
    {
        //TODO CHECK if NEEDED
        return $this->bundleName;
    }

    /**
     * Get the request string.
     *
     * @return null|string
     */
    public function getString()
    {
        $string = null;

        if ($this->request->query->get('string')) {
            $string = $this->request->query->get('string');
        }

        return $string;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = 1;

        if (!empty($this->request->query->get('page'))) {
            $page = $this->request->query->get('page');
        }

        return $page;
    }

    /**
     * Get request parameter.
     *
     * @return array
     */
    public function getRequestParameter()
    {
        return $this->request->query->all();
    }
}

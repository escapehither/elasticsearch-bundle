<?php

/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 27/06/17
 * Time: 21:54
 */
namespace EscapeHither\SearchManagerBundle\Utils;

use Symfony\Component\HttpFoundation\Request;

/**
 * Request Handler utils.
 */
class RequestHandlerUtils
{
    const ARGUMENTS = 'arguments';
    const _PAGINATION_ = 'pagination';
    const _INDEX_ = 'index';
    const _TEMPLATE_ = 'template';
    const _BUNDLE_ = 'bundle';
    const _ACTION_ = 'action';
    const _RESOURCE_ = 'resource';
    const _NAME_ = 'name';
    const _ROOT_CLASS_ = 'rootClass';
    const _NAME_CONFIG_ = 'nameConfig';

    /**
     * @var Request
     */
    protected $request;

    /**
     * Get the request.
     *
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Transform a string from camel_case to underscore.
     *
     * @param string $input The iput.
     *
     * @return string A string lowercase with underscore pattern.
     */
    public static function fromCamelCase($input)
    {
        preg_match_all(
            '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!',
            $input,
            $matches
        );
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match === strtoupper($match) ? strtolower(
                $match
            ) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * Get the bundle name.
     *
     * @param string $paramController The param controller.
     *
     * @return string
     */
    public static function getRootBundle($paramController)
    {
        $rootBundle = '';
        for ($i = 0; $i <= count($paramController) - 3; $i++) {
            $rootBundle .= $paramController[$i];
        }

        return $rootBundle;
    }

    /**
     * Get the root class.
     *
     * @param string $paramController
     *
     * @return string
     */
    public static function getRootClass($paramController)
    {
        $rootClass = '';
        for ($i = 0; $i <= count($paramController) - 3; $i++) {
            $rootClass .= $paramController[$i].'\\';
        }

        return substr($rootClass, 0, -1);
    }

    /**
     * Generate the resource name uses for the template.
     *
     * @param array $attributes The attributes from the request.
     *
     * @return string
     */
    public static function generateResourceViewName($attributes)
    {
        if ("indexAction" === $attributes[self::_ACTION_]) {
            $name = $attributes[self::_NAME_].'s';
        } else {
            $name = $attributes[self::_NAME_];
        }

        return $name;
    }

    /**
     * Get the action ingo
     *
     * @param array  $attributes
     * @param string $type
     *
     * @return null|string
     */
    public static function getInfoFromAction(array $attributes, $type)
    {
        $actionList = ['index', 'new', 'show', 'edit'];
        $suffix = str_replace('Action', '', $attributes[self::_ACTION_]);
        $info = null;

        if (in_array($suffix, $actionList)) {
            if ('path' === $type) {
                $info = $attributes[self::_TEMPLATE_].'/'.$suffix.'.html.twig';
            } elseif ('route' === $type) {
                $info = $attributes[self::_NAME_CONFIG_].'_'.$suffix;
            }
        }

        return $info;
    }

    /**
     * Get attributes.
     *
     * @return mixed
     */
    public function getAttributes()
    {
        $attributes = [];

        if ($this->request) {
            $controllerLink = $this->request->attributes->get('_controller');
            $paramController = explode("\\", $controllerLink);
            $controllerAction = $paramController[count($paramController) - 1];
            $controllerActionTab = explode("::", $controllerAction);
            $attributes[self::_ROOT_CLASS_] = self::getRootClass($paramController);
            $attributes['rootBundle'] = self::getRootBundle($paramController);

            if (!empty($paramController)) {
                $attributes[self::_BUNDLE_] = $paramController[0];
            } else {
                $attributes[self::_BUNDLE_] = null;
            }

            $controller = $controllerActionTab[0];
            $pattern = '/Controller/';
            $replacement = '';
            $attributes[self::_RESOURCE_] = preg_replace(
                $pattern,
                $replacement,
                $controller
            );
            // The resource name available in template.
            $attributes[self::_NAME_] = lcfirst($attributes[self::_RESOURCE_]);
            // The resource name for the template folder.
            $attributes[self::_TEMPLATE_] = strtolower($attributes[self::_RESOURCE_]);
            // The resource name for getting config parameters.
            $attributes[self::_NAME_CONFIG_] = self::fromCamelCase(
                $attributes[self::_RESOURCE_]
            );
            if (!empty($controllerActionTab[1])) {
                $attributes[self::_ACTION_] = $controllerActionTab[1];
            } else {
                $attributes[self::_ACTION_] = null;
            }
            $attributes['_route'] = $this->request->attributes->get('_route');
            $attributes[self::_INDEX_] = $this->getConfig(self::_INDEX_);
            $attributes[self::_PAGINATION_] = $this->getConfig(self::_PAGINATION_);
            $attributes['form'] = $this->getFormConfig();
            $attributes['security'] = $this->getSecurityConfig();
        }


        return $attributes;
    }

    /**
     *Get the configuration.
     *
     * @param string $type
     *
     * @return Null|array
     */
    public function getConfig($type)
    {
        $config = $this->request->attributes->get($type);

        if (isset($config[self::ARGUMENTS])) {
            foreach ($config[self::ARGUMENTS] as $key => $value) {
                $config[self::ARGUMENTS][$key] = $this->request->query->get(
                    $value
                );
            }
        } else {
            $config[self::ARGUMENTS] = null;
        }

        if (!isset($config['method'])) {
            $config['method'] = null;
        }

        return $config;
    }


    /**
     * Get The form Configuration.
     *
     * @return mixed
     */
    public function getFormConfig()
    {
        return $this->request->attributes->get('form');
    }

    /**
     * Get The security Configuration.
     *
     * @return mixed
     */
    public function getSecurityConfig()
    {
        return $this->request->attributes->get('security');
    }

    /**
     *  Generate the theme path.
     *
     * @param array $attributes The request attributes
     *
     * @return null|string
     */
    public function generateThemePath(array $attributes)
    {
        // Check if the template is set in the routing attributes.
        $paramTemplate = $this->request->attributes->get(self::_TEMPLATE_);
        $path = null;

        if (isset($this->request->query) && $this->request->query->get(self::_TEMPLATE_)) {
            $path = $this->request->query->get(self::_TEMPLATE_);
        } elseif (isset($paramTemplate)) {
            return $paramTemplate;
        } else {
            $path = self::getInfoFromAction($attributes, 'path');
        }

        return $path;
    }

    /**
     *Get the route route parameter.
     *
     * @return mixed
     */
    public function getRouteParameter()
    {
        return $this->request->attributes->get('_route_params');
    }

    /**
     * Get the ressource class.
     *
     * @return string
     */
    public function getResourceClass()
    {
        $attributes = $this->getAttributes();
        $resourceClass = $attributes[self::_ROOT_CLASS_].'\Entity\\'.$attributes[self::_RESOURCE_];

        return $resourceClass;
    }
}

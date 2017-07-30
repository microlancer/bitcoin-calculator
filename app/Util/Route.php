<?php

namespace App\Util;

use App\Util\Di;
use App\Util\HeaderParams;

/**
 * @codeCoverageIgnore
 */
class Route
{
    private $resources;
    private $headers;

    public function __construct(HeaderParams $headers)
    {
        $this->resources = [];
        $this->headers = $headers;
    }

    public function addResources(array $resources)
    {
        $this->resources = $resources;
    }

    public function dispatch($unfilteredRequestParams)
    {
        if (!isset($unfilteredRequestParams['q'])) {
            $route = 'index/index';
        } else {
            $route = $unfilteredRequestParams['q'];
        }

        $unfilteredRequestParams['route'] = $route;

        if (in_array($route, $this->resources)) {
            $routeParts = explode('/', $route);
            $controllerName = Route::toControllerName($routeParts[0]);

            if (isset($routeParts[2])) {
                $unfilteredRequestParams['version'] = $routeParts[1];
                $actionName = Route::toControllerActionName($routeParts[2]);
            } elseif (isset($routeParts[1])) {
                $actionName = Route::toControllerActionName($routeParts[1]);
            } else {
                $actionName = Route::toControllerActionName('index');
            }

            $fullyQualifiedControllerName = "App\Controller\\$controllerName";
            $controller = Di::getInstance()->get($fullyQualifiedControllerName);
            $httpParams = Di::getInstance()->create("App\Util\HttpParams");
            $httpParams->setParams($unfilteredRequestParams);
            call_user_func([$controller, $actionName], $httpParams);
        } else {
            $this->headers->setResponseCode(404);
            echo '404 Not Found';
        }
    }

    public function toControllerName($str)
    {
        $routeWords = explode('-', $str);
        foreach ($routeWords as $routeWord) {
            $controllerWords[] = ucfirst($routeWord);
        }
        return implode('', $controllerWords) . 'Controller';
    }

    public function toControllerActionName($str)
    {
        $routeWords = explode('-', $str);
        foreach ($routeWords as $i => $routeWord) {
            if ($i == 0) {
                $actionWords[] = $routeWord;
            } else {
                $actionWords[] = ucfirst($routeWord);
            }
        }
        return implode('', $actionWords) . 'Action';
    }
}

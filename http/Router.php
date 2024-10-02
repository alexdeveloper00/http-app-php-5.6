<?php 
namespace Http;

use Http\RouteBehavior;

class Router {
    private $m_routes = [];

    public function __construct($routes = []) {
        $this->m_routes = $routes;
    }

    private function checkMatch($sMap, $sUri) {
        $pattern = str_replace('/', '\/', $sMap);
        $pattern = preg_replace('/\{(\w+)\}/', '(?<$1>[^\/]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';

        if (preg_match($pattern, $sUri, $matches)) { 
            preg_match_all('/\{([^}]*)\}/', $sMap, $params);
            $result = [];

            foreach ($params[1] as $it => $param) {
                $newPattern = str_replace(['{', '}'], ['(?P<', '>[^\/]+)'], $sMap);
                if (preg_match('~^' . $newPattern . '$~', $sUri, $mtchs)) {
                    if (isset($mtchs[$param])) {
                        $result[$it]['value'] = $mtchs[$param];
                        $result[$it]['placeholder'] = $param;
                    }
                }
            }
        
            return $result;
        }

        return false;
    }
    
    public function getRouteBehavior($request) {
        $routes = $this->m_routes;
        $behavior = new RouteBehavior;

        if (!is_array($routes)) {
            return false;
        }
      
        foreach ($routes as $route) {
            if ($route->hasMap() && $route->hasHandler()) {
                $uri = strtok($request->getRequestUri(), '?');
                if (!is_string($uri)) return false;
                
                $match = $this->checkMatch($route->getMap(), $uri);

                if (false !== $match) {
                    if ($route->hasMiddleware()) {
                        $mware = $route->getMiddleware();

                        if (is_array($mware)) {
                            $que = [];
                        
                            foreach ($mware as $mw) {
                                $que[] = new $mw($request);    
                            }

                            $behavior->setMiddleware($que);
                        } elseif (is_string($mware)) {
                            $behavior->setMiddleware(new $mware($request));
                        }
                    }
                    
                    $behavior->setHandler($route->getHandler());
                    $behavior->setMethod($route->getHandlerMethod());
                    $behavior->setVerbs($route->getVerbs());
                    $behavior->setParams($match);
                    break;
                }
            }
        }

        return $behavior;
    }
}
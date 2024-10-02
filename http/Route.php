<?php 
namespace Http;

class Route {
    private static $m_routes = [];

    private $m_uri;
    private $m_verbs = [];
    private $m_middleware;
    private $m_handler = null;
    private $m_handlerMethod;

    private function __construct() {}
 
    public static function verbs($arr_verbs) {
        $route = new Route();
        $route->m_verbs = $arr_verbs;
        self::$m_routes[] = $route;
        return $route;
    }

    public static function dispatch() {
        return static::$m_routes;
    }

    public function middleware($middleware) {
        if (is_array($middleware) || is_string($middleware)) {
            $this->m_middleware = $middleware;
        }
        
        return $this;
    }

    public function getMiddleware() {
        return $this->m_middleware;
    }

    public function hasMiddleware() {
        return isset($this->m_middleware);
    }

    public function map($map) {
        $this->m_uri = $map;
        return $this;
    }

    public function getMap() {
        if ($this->m_uri) {
            return $this->m_uri;
        }

        return null;
    }

    public function hasMap() {
        return isset($this->m_uri) && is_string($this->m_uri);
    }

    public function handler($handler) {
        if (is_array($handler)) {
            if (class_exists($handler[0]) && method_exists($handler[0], $handler[1])) {
                $instance = new $handler[0];
                $this->m_handler = $instance;
                $this->m_handlerMethod = $handler[1];
            } 
        }

        return $this;
    }

    public function getHandler() {
        if ($this->m_handler) {
            return $this->m_handler;
        }

        return null;
    }

    public function getHandlerMethod() {
        if ($this->m_handlerMethod) {
            return $this->m_handlerMethod;
        }

        return NULL;
    }

    public function hasHandler() {
        return isset($this->m_handler) && $this->m_handler;
    }

    public function getVerbs() {
        return $this->m_verbs;
    }
}
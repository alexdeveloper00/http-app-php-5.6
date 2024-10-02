<?php
namespace Http;

class RouteBehavior {
    private $m_handler;
    private $m_method;
    private $m_middleware;
    private $m_verbs; 
    private $m_params;

    public function __construct() {
        $this->m_handler = null;
        $this->m_method = null;
        $this->m_middleware = null;
        $this->m_verbs = [];
        $this->m_params = [];
    }

    public function setHandler($handler) {
        $this->m_handler = $handler;
    }

    public function getHandler() {
        return $this->m_handler;
    }

    public function hasHandler() {
        return NULL !== $this->m_handler;
    }

    public function setMethod($method) {
        $this->m_method = $method;
    }

    public function getMethod() {
        return $this->m_method;
    }

    public function setVerbs($vtVerbs) {
        $this->m_verbs = $vtVerbs;
    }

    public function getVerbs() {
        return $this->m_verbs;
    }

    public function setParams($vtParams) {
        $this->m_params = $vtParams;
    } 

    public function getParams() {
        return $this->m_params;
    }

    public function setMiddleware($mw) {
        $this->m_middleware = $mw;
    }

    public function getMiddleware() {
        return $this->m_middleware;
    }

    public function hasMiddlewareQue() {
        return is_array($this->m_middleware);
    }

    public function hasMiddleware() {
        return NULL !== $this->m_middleware;
    }
}
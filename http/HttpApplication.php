<?php 

namespace Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Rx\Subject\BehaviorSubject;
use Rx\Observer\CallbackObserver;

use Http\Router;

class HttpApplication {
    private $m_request;
    private $m_response;
    private $m_router;

    public function __construct() {
        global $config;
        $this->m_request = Request::createFromGlobals();
        $this->m_response = new BehaviorSubject(null);
        $this->m_router = new Router($config['routes']);

        $this->response();
    }

    public function run() {
        $behavior = $this->m_router->getRouteBehavior($this->m_request);
        
        if (!$behavior || !$behavior->hasHandler()) {
            $this->m_response->onNext([
                'message' => "Route not found",
                'status' => 404,
            ]);
        } elseif (!in_array($this->m_request->getMethod(), $behavior->getVerbs())) {
            $this->m_response->onNext([
                'message' => 'Method is not allowed',
                'status' => 405,
            ]);
        } else {
            $instance = $behavior->getHandler();
            $method = $behavior->getMethod();
            $params = [];

            // check for middleware
            if ($behavior->hasMiddleware()) {
                $this->callMiddleware($behavior, $this->m_request);
            }

            // method params
            foreach ($behavior->getParams() as $p) {
                $params[$p['placeholder']] = $p['value'];
            }

            // check for dependencies
            $ref = new \ReflectionClass($instance);
            $refMethod = $ref->getMethod($method);
            $dependecies = [];
            
            foreach ($refMethod->getParameters() as $p) {
                if ($p->getClass()) {
                    $depClass = $p->getClass()->getName();
                    $depInstance = new $depClass();
                    $dependecies[$p->getName()] = $depInstance;
                }
            }

            $controllerResponse = call_user_func_array([$instance, $method], array_merge($dependecies, $params));
            $this->m_response->onNext($controllerResponse);
        }
    }

    private function response() {
        return $this->m_response->distinctUntilChanged()->subscribe(new CallbackObserver(function ($content) {
            if (!is_array($content)) {
                return;
            }

            $response = new JsonResponse();
            $status = (isset($content['status'])) ? (int)$content['status'] : 200;
            $response->setContent(json_encode($content));
            $response->setStatusCode($status);
            $response->prepare($this->m_request);
            $response->send();

            return $this->m_response->onCompleted();
        }, NULL, function () {
            $this->m_response->unsubscribe();         
        }));
    }

    private function callMiddleware($behavior, $request) {
        if ($behavior->hasMiddlewareQue()) {
            foreach ($behavior->getMiddleware() as $it) {
                $response = call_user_func_array([$it, 'handle'], ['request' => $request]);
             
                if (NULL !== $response) {
                    return $this->m_response->onNext($response);
                }
            }
        }

        $response = call_user_func_array([$behavior->getMiddleware(), 'handle'], ['request' => $request]);
        return $this->m_response->onNext($response);
    }
}
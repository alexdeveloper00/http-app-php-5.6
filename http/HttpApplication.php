<?php 

namespace Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Illuminate\Database\Capsule\Manager as Capsule;

use Rx\Subject\BehaviorSubject;
use Rx\Observer\CallbackObserver;

use Http\Router;

class HttpApplication {
    private $m_request;
    private $m_response;
    private $m_router;
    private $m_db_connections = [];
    private $m_capsule;

    public function __construct() {
        global $config;
        $this->m_request = Request::createFromGlobals();
        $this->m_response = new BehaviorSubject(null);
        $this->m_router = new Router($config['routes'], $config['niceLinks']);
        $this->m_db_connections = isset($config['db']) && is_array($config['db']) ? $config['db'] : NULL;
        $this->response();
    }

    public function run() {
        if (!$this->m_db_connections) {
            $this->m_response->onNext([
                'message' => 'Cannot connect to database. (FATAL BACKEND ERROR)',
                'status' => 500
            ]);
        }

        $capsule = new Capsule(null);
        foreach ($this->m_db_connections as $name => $conn) {
            $capsule->addConnection([
                'driver' => $conn['driver'],
                'host' => $conn['host'],
                'username' => $conn['user'],
                'password' => $conn['password'],
                'database' => $conn['dbname'],
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
            ], $name);
        }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

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
                $this->callMiddleware($behavior);
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
                    if ($depClass === Request::class) {
                        $depInstance = $this->m_request;
                    } else {
                        $depInstance = new $depClass();
                    }
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

    private function callMiddleware($behavior) {
        if ($behavior->hasMiddlewareQue()) {
            foreach ($behavior->getMiddleware() as $it) {
                $response = call_user_func_array([$it, 'handle'], ['request' => $this->m_request]);
             
                if (NULL !== $response) {
                    return $this->m_response->onNext($response);
                }
            }
        }

        $response = call_user_func_array([$behavior->getMiddleware(), 'handle'], ['request' => $this->m_request]);
        return $this->m_response->onNext($response);
    }
}
<?php

namespace Graphite\Component\Dispatcher;

use Closure;

class Dispatcher
{
    /**
     * Current release version.
     * 
     * @var string
     */
    private static $version = '1.0.0';

    /**
     * Supported request methods.
     * 
     * @var array
     */
    private static $supported_verbs = array(
        'get',
        'post',
        'put',
        'patch',
        'delete',
        'head',
    );

    /**
     * Supported http status codes.
     * 
     * @var array
     */
    private static $supported_codes = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',
        '103' => 'Early Hints',
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '207' => 'Multi-Status',
        '300' => 'Mutiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',
        '308' => 'Permanent Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range No Satisfiable',
        '417' => 'Expectation Failed',
        '421' => 'Misdirected Request',
        '422' => 'Unprocessable Entity',
        '426' => 'Upgrade Required',
        '428' => 'Precondition Required',
        '429' => 'Too Many Request',
        '431' => 'Request Header Fields Too Large',
        '451' => 'Unavailable For Legal Reasons',
        '498' => 'Invalid Token',
        '499' => 'Token Required',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
        '511' => 'Network Authentication Required',
    );

    /**
     * Store registered routes.
     * 
     * @var array
     */
    private static $routes = array();

    /**
     * Store request object.
     * 
     * @var \Graphite\Component\Dispatcher\Request
     */
    private $request;

    /**
     * HTTP status code.
     * 
     * @var int
     */
    private $code = 500;

    /**
     * Store matched route data.
     * 
     * @var array
     */
    private $route;

    /**
     * Middlewares executed before action is executed.
     * 
     * @var array
     */
    private $middlewares = array();

    /**
     * Middlewares executed after action is executed.
     * 
     * @var array
     */
    private $after_middlewares = array();

    /**
     * Store callback actions for http errors.
     * 
     * @var array
     */
    private $callbacks = array();

    /**
     * Store default error callback.
     * 
     * @var mixed
     */
    private $default_callback;

    /**
     * Store event callbacks.
     * 
     * @var array
     */
    private $events = array(
        'oncreate'                  => null,
        'onbeforemiddleware'        => null,
        'onmiddlewareexecute'       => null,
        'onmiddlewareabort'         => null,
        'onbeforeaction'            => null,
        'onafteraction'             => null,
        'onredirect'                => null,
        'onbodysent'                => null,
        'ondestroy'                 => null,
        'onerror'                   => null,
        'onroutematched'            => null,
    );

    /**
     * Store header informations.
     * 
     * @var array
     */
    private $headers = array();

    /**
     * Store actions that uses closures.
     * 
     * @var array
     */
    private $closures = array();

    /**
     * Determine if middleware is running.
     * 
     * @var bool
     */
    private $middleware = false;

    /**
     * Redirection route.
     * 
     * @var string
     */
    private $redirect;

    /**
     * Determine if service is available.
     * 
     * @var bool
     */
    private $unavailable = false;

    /**
     * Construct a new instance of dispatcher.
     * 
     * @return  void
     */
    public function __construct()
    {
        $this->request  = new Request($this);

        $this->setDefaultErrorCallback(function($request) {
            return $request->responseMessage();
        });
    }

    /**
     * Set callback to call when request has started.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onCreate($callback)
    {
        $this->events['oncreate'] = $callback;

        return $this;
    }

    /**
     * Set callback to call when request is destroyed.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onDestroy($callback)
    {
        $this->events['ondestroy'] = $callback;

        return $this;
    }

    /**
     * Set callback to call each time an error occured.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onError($callback)
    {
        $this->events['onerror'] = $callback;

        return $this;
    }

    /**
     * Set callback to call each time route matches.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onRouteMatched($callback)
    {
        $this->events['onroutematched'] = $callback;

        return $this;
    }

    /**
     * Event callback before running middleware.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onBeforeMiddleware($callback)
    {
        $this->events['onbeforemiddleware'] = $callback;

        return $this;
    }

    /**
     * Event callback each time middleware runs.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onMiddlewareExecute($callback)
    {
        $this->events['onmiddlewareexecute'] = $callback;

        return $this;
    }

    /**
     * Event callback each time middleware is aborted.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onMiddlewareAbort($callback)
    {
        $this->events['onmiddlewareabort'] = $callback;

        return $this;
    }

    /**
     * Event callback before running action.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onBeforeAction($callback)
    {
        $this->events['onbeforeaction'] = $callback;

        return $this;
    }

    /**
     * Event callback after running the action.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onAfterAction($callback)
    {
        $this->events['onafteraction'] = $callback;

        return $this;
    }

    /**
     * Event callback when redirection is triggered.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onRedirect($callback)
    {
        $this->events['onredirect'] = $callback;

        return $this;
    }

    /**
     * Event callback after sending the body.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function onBodySent($callback)
    {
        $this->events['onbodysent'] = $callback;

        return $this;
    }

    /**
     * Action to execute if an error occurred.
     * 
     * @param   int $code
     * @param   mixed $callback
     * @return  $this
     */
    public function setErrorCallback(int $code, $callback)
    {
        $this->callbacks['code_' . $code] = $callback;

        return $this;
    }

    /**
     * Default action to execute if an error occured.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function setDefaultErrorCallback($callback)
    {
        $this->default_callback = $callback;

        return $this;
    }

    /**
     * Register middlewares to execute before the action.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function middleware($callback)
    {
        $this->middlewares[] = $callback;

        return $this;
    }

    /**
     * Register middlewares to execute after the action.
     * 
     * @param   mixed $callback
     * @return  $this
     */
    public function afterMiddleware($callback)
    {
        $this->after_middlewares[] = $callback;

        return $this;
    }

    /**
     * Return request object.
     * 
     * @return  \Graphite\Component\Dispatcher\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Factory for registering route.
     * 
     * @param   string $uri
     * @param   mixed $method
     * @param   mixed $action
     * @return  $this
     */
    private function makeRoute(string $uri, $method, $action)
    {
        $closure    = ($action instanceof Closure);
        $hash       = md5(str_random());

        if($closure)
        {
            $this->closures[$hash] = $action;
        }
        
        self::$routes[] = array(
            'hash'      => $hash,
            'uri'       => $uri,
            'method'    => $method,
            'action'    => !$closure ? $action : null,
            'closure'   => $closure,
            'resource'  => array(),
        );

        return $this;
    }

    /**
     * Register route that use any request method.
     * 
     * @param   string $uri
     * @param   mixed $action
     * @return  $this
     */
    public function any(string $uri, $action)
    {
        return $this->makeRoute($uri, '*', $action);
    }

    /**
     * Register a route that use GET method.
     * 
     * @param   string $uri
     * @param   mixed $action
     * @return  $this
     */
    public function get(string $uri, $action)
    {
        return $this->makeRoute($uri, 'get', $action);
    }

    /**
     * Register a route that uses POST method.
     * 
     * @param   string $uri
     * @param   mixed $action
     * @return  $this
     */
    public function post(string $uri, $action)
    {
        return $this->makeRoute($uri, 'post', $action);
    }

    /**
     * Register a route that uses PUT method.
     * 
     * @param   string $uri
     * @param   mixed $action
     * @return  $this
     */
    public function put(string $uri, $action)
    {
        return $this->makeRoute($uri, 'put', $action);
    }

    /**
     * Register a route that uses PATCH method.
     * 
     * @param   string $uri
     * @param   mixed $action
     * @return  $this
     */
    public function patch(string $uri, $action)
    {
        return $this->makeRoute($uri, 'patch', $action);
    }

    /**
     * Register a route that uses DELETE method.
     * 
     * @param   string $uri
     * @param   mixed $action
     * @return  $this
     */
    public function delete(string $uri, $action)
    {
        return $this->makeRoute($uri, 'delete', $action);
    }

    /**
     * Register a route that has multiple methods.
     * 
     * @param   array $methods
     * @param   string $uri
     * @param   mixed $action
     * @return  $this
     */
    public function match(array $methods, string $uri, $action)
    {
        return $this->makeRoute($uri, $methods, $action);
    }

    /**
     * Return http status response code.
     * 
     * @return  int
     */
    public function responseCode()
    {
        return $this->code;
    }

    /**
     * Return http status response message.
     * 
     * @return  string
     */
    public function responseMessage()
    {
        return self::$supported_codes[$this->code];
    }

    /**
     * Abort request and return error.
     * 
     * @param   int $code
     * @return  $this
     */
    public function abort(int $code)
    {
        $action = function() {};

        if($this->middleware)
        {
            $this->runEvent('onmiddlewareabort', array(
                $this->request,
            ));
        }

        if($code !== 200)
        {
            $callback   = 'code_' . $code;
            $action     = $this->default_callback;

            if(array_key_exists($callback, $this->callbacks))
            {
                $action = $this->callbacks[$callback];
            }

            http_response_code($code);

            $this->runEvent('onerror', array(
                $this->request,
            ));
        }

        $this->code     = $code;
        $body           = $this->runFn($action, array(
            $this->request,
        ));

        $this->redirection();
        $this->sendBody($body);
        $this->terminate();

        return $this;
    }

    /**
     * Set the service as unavailable.
     * 
     * @return  $this
     */
    public function down()
    {
        $this->unavailable = true;

        return $this;
    }

    /**
     * Set the service as available.
     * 
     * @return  $this
     */
    public function up()
    {
        $this->unavailable = false;

        return $this;
    }

    /**
     * Redirect route to new route.
     * 
     * @param   string $location
     * @return  $this
     */
    public function redirect(string $location)
    {
        $this->redirect = $location;

        return $this;
    }

    /**
     * Execute a closure or a class.
     * 
     * @param   mixed $action
     * @return  mixed
     */
    private function runFn($action, array $arguments = [])
    {
        if($action instanceof Closure)
        {
            return $action(...$arguments);
        }
        else if(is_string($action))
        {
            $split = str_break($action, '@');

            return (new $split[0](...$arguments))->{$split[1]}(...$arguments);
        }
    }

    /**
     * Run event callback.
     * 
     * @param   string $key
     * @param   array $arguments
     * @return  void
     */
    private function runEvent(string $key, array $arguments = [])
    {
        if(array_key_exists($key, $this->events))
        {
            $event = $this->events[$key];

            if(!is_null($event))
            {
                $this->runFn($event, $arguments);
            }
        }
    }

    /**
     * Run the router operations.
     * 
     * @return  void
     */
    public function run()
    {
        $this->runEvent('oncreate', array(
            $this->request,
        ));

        $this->service();
        $this->testRequestMethod();

        if(is_null($this->route))
        {
            if(count(self::$routes) == 0)
            {
                $this->abort(404);
            }

            $this->findRoutes();
        }

        $this->runMiddlewares();
        
        $body = $this->runActions();

        if(empty($body) || is_null($body))
        {
            $this->abort(204);
        }

        $this->runEvent('onafteraction', array(
            $this->request,
        ));

        $this->runAfterMiddlewares();
        $this->setHeaders($this->headers);
        $this->redirection();
        $this->sendBody($body);
        $this->terminate();
    }

    /**
     * Check if redirection is requested.
     * 
     * @return  void
     */
    private function redirection()
    {
        if(!is_null($this->redirect))
        {
            $this->runEvent('onredirect', array(
                $this->request,
            ));

            header('location: ' . $this->redirect);
            exit;
        }
    }

    /**
     * Check if service is available.
     * 
     * @return  void
     */
    private function service()
    {
        if($this->unavailable)
        {
            $this->abort(503);
        }
    }

    /**
     * Find matches from all registered routes.
     * 
     * @return  void
     */
    private function findRoutes()
    {
        $matches    = array();
        $method     = strtolower($this->request()->method());
        $uri1       = $this->splitUri($this->request()->uri());

        for($i = 0; $i <= count(self::$routes) - 1; $i++)
        {
            $params     = array();
            $route      = self::$routes[$i];
            $uri2       = $this->splitUri($route['uri']);
            $count      = 0;

            if(sizeof($uri1) == sizeof($uri2))
            {
                for($j = 0; $j <= count($uri2) - 1; $j++)
                {
                    if(strtolower($uri1[$j]) == strtolower($uri2[$j]))
                    {
                        $count++;
                    }
                    else
                    {
                        if($uri1[$j] !== '')
                        {
                            if(str_starts_with($uri2[$j], '{') && str_ends_with($uri2[$j], '}'))
                            {
                                $params[str_move($uri2[$j], 1, 1)] = $uri1[$j];
                                $count++;
                            }
                        }
                    }
                }
            }

            if($count == sizeof($uri2))
            {
                if(!empty($params))
                {
                    $route['resource'] = $params;
                }

                $matches[] = $route;
            }
        }

        if(!empty($matches))
        {
            foreach($matches as $route)
            {
                $verb       = $route['method'];
                $methods    = is_string($verb) ? array($verb) : $verb;
                
                if(in_array($method, $methods) || $verb == '*')
                {
                    $this->route = $route;
                    $this->runEvent('onroutematched', array(
                        $this->request,
                    ));

                    return;
                }
            }

            $this->abort(405);
        }
        else
        {
            $this->abort(404);
        }
    }

    /**
     * Split the URI by slashes.
     * 
     * @param   string $uri
     * @return  array
     */
    private function splitUri(string $uri)
    {
        if($uri != '/')
        {
            if(str_ends_with($uri, '/'))
            {
                $uri = str_move_right($uri, 1);
            }

            if(str_starts_with($uri, '/'))
            {
                $uri = str_move_left($uri, 1);
            }
        }
        else
        {
            $uri = '';
        }

        return explode('/', $uri);
    }

    /**
     * Test if request method is supported or valid.
     * 
     * @return  void
     */
    private function testRequestMethod()
    {
        $method = strtolower($this->request->method());
        
        if(!in_array($method, self::$supported_verbs))
        {
            $this->abort(405);
        }
    }

    /**
     * Run middlewares before running the action.
     * 
     * @return  void
     */
    private function runMiddlewares()
    {
        $this->middleware = true;

        $this->runEvent('onbeforemiddleware', array(
            $this->request,
        ));

        $this->runMiddleware($this->middlewares, 0, array(
            $this->request,
        ));

        $this->middleware = false;
    }

    /**
     * Run middlewares after running the action.
     * 
     * @return  void
     */
    private function runAfterMiddlewares()
    {
        $this->middleware = true;
        $this->runMiddleware($this->after_middlewares, 0, array(
            $this->request,
        ));
        $this->middleware = false;
    }

    /**
     * Run middlewares one at a time.
     * 
     * @param   array $middlewares
     * @param   int $index
     * @param   array $arguments
     * @return  void
     */
    private function runMiddleware(array $middlewares, int $index, array $arguments)
    {
        if($index < sizeof($middlewares))
        {
            $this->runFn($middlewares[$index], array_merge($arguments, array(
                $index + 1,
            )));

            $this->runEvent('onmiddlewareexecute', array(
                $this->request,
                $index
            ));

            $this->runMiddleware($middlewares, $index + 1, $arguments);
        }
    }

    /**
     * Run route action closures or controller classes.
     * 
     * @return  string
     */
    private function runActions()
    {
        $route  = $this->route;

        $this->code = 200;
        $this->runEvent('onbeforeaction', array(
            $this->request,
        ));

        if($route['closure'] && !is_null($route['hash']) && array_key_exists($route['hash'], $this->closures))
        {
            return $this->runFn($this->closures[$route['hash']], array(
                $this->request,
            ));
        }
        else
        {
            return $this->runFn($route['action'], array(
                $this->request,
            ));
        }
    }

    /**
     * Set http response headers.
     * 
     * @param   array $headers
     * @return  void
     */
    private function setHeaders(array $headers)
    {
        foreach($headers as $header)
        {
            header($header['key'] . ': ' . $header['content']);
        }
    }

    /**
     * Add new request header information.
     * 
     * @param   string $key
     * @param   string $content
     * @return  $this
     */
    public function setHeader(string $key, string $content)
    {
        $this->headers[] = array(
            'key'       => $key,
            'content'   => $content,
        );

        return $this;
    }

    /**
     * Send the response body to the users.
     * 
     * @param   string $body
     * @return  void
     */
    private function sendBody(string $body)
    {
        $this->runEvent('onbodysent', array(
            $this->request,
        ));

        echo $body;
    }

    /**
     * Terminate request.
     * 
     * @return  void
     */
    private function terminate()
    {
        $this->runEvent('ondestroy', array(
            $this->request,
        ));

        exit(0);
    }

    /**
     * Return matched route data.
     * 
     * @return  array
     */
    public function route()
    {
        return $this->route;
    }

    /**
     * Set route data.
     * 
     * @param   array $data
     * @return  void
     */
    public function setRouteData(array $data)
    {
        $this->route = $data;
    }

    /**
     * Return list of supported request methods.
     * 
     * @return  array
     */
    public static function supportedVerbs()
    {
        return self::$supported_verbs;
    }

    /**
     * Return current release version.
     * 
     * @return  string
     */
    public static function version()
    {
        return self::$version;
    }
}
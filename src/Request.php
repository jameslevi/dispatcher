<?php

namespace Graphite\Component\Dispatcher;

use Graphite\Component\Objectify\Objectify;

class Request
{
    /**
     * Store dispatcher instance.
     * 
     * @var \Graphite\Component\Dispatcher\Dispatcher
     */
    private $context;

    /**
     * Store server information object.
     * 
     * @var \Graphite\Component\Objectify\Objectify
     */
    private $server;

    /**
     * Store GET parameter object.
     * 
     * @var \Graphite\Component\Objectify\Objectify
     */
    private $get;

    /**
     * Store POST parameter object.
     * 
     * @var \Graphite\Component\Objectify\Objectify
     */
    private $post;

    /**
     * Store raw JSON body from request.
     * 
     * @var \Graphite\Component\Objectify\Objectify
     */
    private $raw;

    /**
     * Construct a new request object.
     * 
     * @param   \Graphite\Component\Dispatcher\Dispatcher $context
     * @return  void
     */
    public function __construct(Dispatcher $context)
    {
        $this->context      = $context;
        $this->server       = new Objectify($_SERVER, true);
        $this->get          = new Objectify($_GET, true);
        $this->post         = new Objectify($_POST, true);
        $this->raw          = new Objectify(json_decode(file_get_contents("php://input"), true) ?? array(), true);
    }

    /**
     * Return http status response code.
     * 
     * @return  int
     */
    public function responseCode()
    {
        return $this->context->responseCode();
    }

    /**
     * Return http status response message.
     * 
     * @return  string
     */
    public function responseMessage()
    {
        return $this->context->responseMessage();
    }

    /**
     * Return route data if available.
     * 
     * @return  array
     */
    public function route()
    {
        return $this->context->route();
    }

    /**
     * Return request parameter from URI.
     * 
     * @param   string $name
     * @return  mixed
     */
    public function __get(string $name)
    {
        $route = $this->route();

        if(!is_null($route))
        {
            $resource = $route['resource'];

            if(array_key_exists($name, $resource))
            {
                return $resource[$name];
            }
        }
    }

    /**
     * Return the request method.
     * 
     * @return  string
     */
    public function method()
    {
        $method = strtoupper($this->server->get('REQUEST_METHOD'));
    
        if($method === 'POST' && $this->post->has('verb'))
        {
            $method = strtoupper($this->post('verb'));
        }

        return $method;
    }

    /**
     * return the request uri.
     * 
     * @return  string
     */
    public function uri()
    {
        return explode('?', $this->server->get('REQUEST_URI'))[0];
    }

    /**
     * Return the server port.
     * 
     * @return  string
     */
    public function port()
    {
        return $this->server->get('SERVER_PORT');
    }

    /**
     * Return the current server protocol.
     * 
     * @return  string
     */
    public function protocol()
    {
        return $this->server->get('SERVER_PROTOCOL');
    }

    /**
     * Determine if request is through secure connection.
     * 
     * @return  bool
     */
    public function secure()
    {
        $https = $this->server->get('HTTPS');

        return (!empty($https) && $https != 'off') || $this->port() == 443;
    }

    /**
     * Determine if request is through localhost.
     * 
     * @return  bool
     */
    public function localhost()
    {
        return in_array($this->server->get('REMOTE_ADDR'), array('127.0.0.1', '::1'));
    }

    /**
     * Determine if request is through XMLHttpRequest.
     * 
     * @return  bool
     */
    public function xmlHttpRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Return GET parameter value.
     * 
     * @param   string $key
     * @param   mixed $default
     * @return  mixed
     */
    public function get(string $key, $default = null)
    {
        $value = $this->get->get($key);

        if(is_null($value))
        {
            $value = $default;
        }

        return $value;
    }

    /**
     * Return POST parameter value.
     * 
     * @param   string $key
     * @param   mixed $default
     * @return  mixed
     */
    public function post(string $key, $default = null)
    {
        $value = $this->post->get($key);

        if(is_null($value) && $this->raw->has($key))
        {
            $value = $this->raw->get($key);
        }

        if(is_null($value))
        {
            $value = $default;
        }

        return $value;
    }
}
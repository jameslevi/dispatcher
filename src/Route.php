<?php

namespace Graphite\Component\Dispatcher;

use Closure;
use Graphite\Component\Objectify\Objectify;

class Route
{
    /**
     * Store route data as object.
     * 
     * @var \Graphite\Component\Objectify\Objectify
     */
    private $data;

    /**
     * Construct a new route data object.
     * 
     * @param   string $uri
     * @param   mixed $method
     * @param   mixed $action
     * @return  void
     */
    public function __construct(string $uri, $method, $action)
    {
        $this->data = new Objectify(array());
        $this->add('hash', md5(str_random()));
        $this->add('uri', $uri);
        $this->add('method', $method);
        $this->add('closure', $action instanceof Closure);
        $this->add('action', !$this->get('closure') ? $action : null);
        $this->add('resource', array());
        $this->add('unavailable', false);
    }

    /**
     * Add a new route data.
     * 
     * @param   string $key
     * @param   mixed $value
     * @return  $this
     */
    public function add(string $key, $value)
    {
        $this->data->add($key, $value);

        return $this;
    }

    /**
     * Make changes to route data.
     * 
     * @param   string $key
     * @param   mixed $value
     * @return  $this
     */
    private function set(string $key, $value)
    {
        $this->data->set($key, $value);

        return $this;
    }

    /**
     * Return route data.
     * 
     * @param   string $key
     * @return  mixed
     */
    public function get(string $key)
    {
        return $this->data->get($key);
    }

    /**
     * Set unavailable status.
     * 
     * @return  $this
     */
    public function down()
    {
        return $this->set('unavailable', true);
    }

    /**
     * Remove unavailable status.
     * 
     * @return  $this
     */
    public function up()
    {
        return $this->set('unavailable', false);
    }

    /**
     * Return route data as array.
     * 
     * @return  array
     */
    public function getData()
    {
        return $this->data->toArray();
    }
}
<?php

namespace Encore\Redis\Routing;

use Encore\Redis\Auth\Container;

class Request
{
    protected $command = '';

    //protected $key = '';

    protected $parameters = [];

    protected $connection = null;

    public function __construct($command, $parameters = [])
    {
        $this->command    = strtoupper($command);
        //$this->key        = array_shift($parameters);
        $this->parameters = $parameters;
    }

    public function command($command = null)
    {
        if (is_string($command)) {
            $this->command = $command;
            return;
        }

        return $this->command;
    }

    public function parameters($parameters = null)
    {
        if (is_array($parameters)) {
            $this->parameters = $parameters;
            return;
        }

        return $this->parameters;
    }

    public function parameter($index)
    {
        return isset($this->parameters[$index]) ? $this->parameters[$index] : null;
    }

    public function path()
    {
        $pattern = trim($this->parameter(0), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * Get the current encoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function connection()
    {
        return $this->connection;
    }

    public function authenticated()
    {
        return Container::has($this->connection->id);
    }

    public function isCommand($command)
    {
        return $this->command == strtoupper($command);
    }

    public function __get($key)
    {
        $all = $this->parameters();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }
    }

}

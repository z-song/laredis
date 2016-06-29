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

    public function command()
    {
        return $this->command;
    }

//    public function key()
//    {
//        return $this->key;
//    }

    public function parameters()
    {
        return $this->parameters;
    }

//    public function path()
//    {
//        $pattern = trim($this->key(), '/');
//
//        return $pattern == '' ? '/' : $pattern;
//    }

    /**
     * Get the current encoded path info for the request.
     *
     * @return string
     */
//    public function decodedPath()
//    {
//        return rawurldecode($this->path());
//    }

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

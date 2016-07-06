<?php

namespace Encore\Laredis\Routing;

class Request
{
    /** Command of this request.
     *
     * @var string
     */
    protected $command = '';

    /**
     * Parameters of the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Connection of this request.
     *
     * @var \Encore\Laredis\Server\Connection
     */
    protected $connection = null;

    /**
     * Create a new Request instance.
     *
     * @param string $command
     * @param array $parameters
     */
    public function __construct($command, $parameters = [])
    {
        $this->command    = strtoupper($command);
        $this->parameters = $parameters;
    }

    /**
     * Get or Set the command of the request.
     *
     * @param null $command
     * @return string|void
     */
    public function command($command = null)
    {
        if (is_string($command)) {
            $this->command = $command;
            return;
        }

        return $this->command;
    }

    /**
     * Get or Set the parameters of the request.
     *
     * @param null $parameters
     * @return array|void
     */
    public function parameters($parameters = null)
    {
        if (is_array($parameters)) {
            $this->parameters = $parameters;
            return;
        }

        return $this->parameters;
    }

    /**
     * Get parameter by given index.
     *
     * @param mixed $index
     * @return null
     */
    public function parameter($index)
    {
        return isset($this->parameters[$index]) ? $this->parameters[$index] : null;
    }

    /**
     * @return string
     */
    public function key()
    {
        $pattern = trim($this->parameter(0), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    public function decodedKey()
    {
        return rawurldecode($this->key());
    }

    /**
     * Set connection.
     *
     * @param $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get connection of the request.
     *
     * @return \Encore\Laredis\Server\Connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Getter of parameters of th request.
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        $all = $this->parameters();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }
    }
}

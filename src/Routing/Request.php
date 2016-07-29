<?php

namespace Encore\Laredis\Routing;

class Request
{
    /** Command of this request.
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
     * @param array  $parameters
     */
    public function __construct($command, $parameters = [])
    {
        $this->command = strtoupper($command);
        $this->parameters = $parameters;
    }

    /**
     * Get or Set the command of the request.
     *
     * @return string
     */
    public function command()
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Get parameters of the request.
     *
     * @return array
     */
    public function parameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters of the request.
     *
     * @param array $parameters
     *
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Get parameter by given index.
     *
     * @param mixed $index
     *
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
     *
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

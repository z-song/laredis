<?php

namespace Encore\Redis\Server;

class Server
{
    /**
     * @var null|string
     */
    public $socketName = '';

    /**
     * Context of socket.
     *
     * @var resource
     */
    protected $context = null;

    /**
     * Default backlog. Backlog is the maximum length of the queue of pending connections.
     *
     * @var int
     */
    const DEFAUL_BACKLOG = 1024;

    /**
     * Server socket.
     *
     * @var null
     */
    protected $server = null;

    /**
     * @var EventLoop|null
     */
    public static $eventLoop = null;

    /**
     * @var array`
     */
    protected $connections = [];

    /**
     * Create a instance of Server
     *
     * @param string $socketName
     * @param $contextOption
     */
    public function __construct($socketName = 'tcp://localhost:6379', $contextOption = [])
    {
        $this->socketName = $socketName;

        $this->context = stream_context_create($contextOption);

        static::$eventLoop = new EventLoop();
    }

    public function setOptions($options = [])
    {
        
    }

    public function listen()
    {
        $flags  = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        $errno  = 0;
        $errmsg = '';

        $this->server = stream_socket_server($this->socketName, $errno, $errmsg, $flags, $this->context);

        if (!$this->server) {
            throw new \Exception($errmsg);
        }

        // Try to open keepalive for tcp and disable Nagle algorithm.
        if (function_exists('socket_import_stream')) {
            $socket = socket_import_stream($this->server);
            @socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
            @socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
        }

        // Non blocking.
        stream_set_blocking($this->server, 0);
    }

    public function start()
    {
        $this->listen();

        static::$eventLoop->add($this->server, EventLoop::EV_READ, array($this, 'acceptConnection'));

        static::$eventLoop->loop();
    }

    /**
     * Accept a connection.
     *
     * @param resource $socket
     * @return void
     */
    public function acceptConnection($socket)
    {
        // Accept a connection on server socket.
        $new_socket = @stream_socket_accept($socket, 0, $remoteAddress);
        // Thundering herd.
        if (false === $new_socket) {
            return;
        }

        // TcpConnection.
        $connection                         = new Connection($new_socket, $remoteAddress);
        $this->connections[$connection->id] = $connection;
    }

    public function __call($method, $arguments)
    {
        exit("Usage: php artisan {start|stop|restart|reload|status|kill}\n");
    }

    public static function log()
    {
        call_user_func_array('dump', func_get_args());
    }
}
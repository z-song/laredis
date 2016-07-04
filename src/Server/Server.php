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
     * @var array
     */
    protected $connections = [];

    /**
     * The file to store process PID.
     *
     * @var string
     */
    protected static $pidFile = '';

    /**
     * @var null
     */
    protected static $pid = null;

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

        static::init();
    }

    public static function init()
    {
        // Pid file.
        if (empty(self::$pidFile)) {
            self::$pidFile = sys_get_temp_dir() . "/laravel-redis.pid";
        }
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
        echo "Service starting ...\r\n";

        static::daemonize();

        $this->savePid();
        $this->listen();

        //static::$eventLoop = new EventLoop();
        static::$eventLoop = new Libevent();

        static::$eventLoop->add($this->server, EventLoop::EV_READ, [$this, 'acceptConnection']);
        static::$eventLoop->loop();
    }

    protected function savePid()
    {
        self::$pid = posix_getpid();
        if (false === @file_put_contents(self::$pidFile, self::$pid)) {
            throw new Exception('can not save pid to ' . self::$pidFile);
        }
    }

    /**
     * Stop the server.
     */
    public function stop()
    {
        echo "Service stopping ...\r\n";

        exec("ps aux | grep 'artisan redis-server start' | grep -v grep | awk '{print $2}' |xargs kill -SIGINT");
        exec("ps aux | grep 'artisan redis-server start' | grep -v grep | awk '{print $2}' |xargs kill -SIGKILL");
    }

    /**
     * Restart the server.
     */
    public function restart()
    {
        $this->stop();
        usleep(500);

        echo "Service starting ...\r\n";
        exec("php ".base_path('artisan')." redis-server start -d > /dev/null &");

        exit();
    }

    /**
     * Accept a connection.
     *
     * @param resource $serverSocket
     * @return void
     */
    public function acceptConnection($serverSocket)
    {
        // Accept a connection on server socket.
        $socket = @stream_socket_accept($serverSocket, 0, $remoteAddress);
        // Thundering herd.
        if (false === $socket) {
            return;
        }

        // TcpConnection.
        $connection = new Connection($socket, $remoteAddress);
        $connection->server = $this;

        $this->connections[$connection->id] = $connection;
    }

    /**
     * Close Connection.
     *
     * @param $id
     */
    public function closeConnection($id)
    {
        unset($this->connections[$id]);
    }

    protected static function isDaemonize()
    {
        return config('redis-server.daemonize') || (isset($_SERVER['argv'][3]) && $_SERVER['argv'][3] == '-d');
    }

    protected static function daemonize()
    {
        if (! static::isDaemonize()) {
            return;
        }

        umask(0);
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new \Exception('fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }
        if (-1 === posix_setsid()) {
            throw new \Exception("setsid fail");
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new \Exception("fork fail");
        } elseif (0 !== $pid) {
            exit(0);
        }
    }

    /**
     * Server requires password.
     *
     * @return bool
     */
    public static function requirePass()
    {
        $passwords = (array) config('redis-server.password');

        return ! empty($passwords);
    }

    public static function validatePassword($password)
    {
        $passwords  = (array) config('redis-server.password');

        return in_array($password, $passwords);
    }

    public function __call($method, $arguments)
    {
        exit("Usage: php artisan {start|stop|restart}\n");
    }

    public static function log()
    {
        call_user_func_array('dump', func_get_args());
    }
}

<?php

namespace Encore\Laredis\Server;

use Encore\Laredis\Server\EventLoop\Libevent;
use Encore\Laredis\Server\EventLoop\Select;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Server
{
    /**
     * Laredis Version.
     */
    const VERSION = '0.1';

    /**
     * @var null|string
     */
    public $socketName = '';

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $port;

    /**
     * Context of socket.
     *
     * @var resource
     */
    protected $context = null;

    /**
     * Server socket.
     *
     * @var resource
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
     * @var int
     */
    protected static $pid = null;

    /**
     * @var Logger
     */
    public static $logger = null;

    /**
     * Create a instance of Server.
     *
     * @param string $socketName
     * @param $contextOption
     */
    public function __construct($socketName = 'tcp://localhost:6379', $contextOption = [])
    {
        $this->socketName = $socketName;
        $this->context = stream_context_create($contextOption);

        $components = parse_url($socketName);

        $this->ip = $components['host'];
        $this->port = $components['port'];

        $this->init();
    }

    /**
     * Initialize.
     */
    public function init()
    {
        static::$logger = new Logger('laredis');
        static::$logger->pushHandler(new StreamHandler(config('laredis.logFile')));
        static::$logger->pushHandler(new StreamHandler('php://stdout'));

        // Pid file.
        if (empty(static::$pidFile)) {
            static::$pidFile = sys_get_temp_dir().'/laredis.pid';
        }
    }

    /**
     * Listen.
     *
     * @throws Exception
     */
    public function listen()
    {
        $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        $errno = 0;
        $errmsg = '';

        $this->server = stream_socket_server($this->socketName, $errno, $errmsg, $flags, $this->context);

        if (!$this->server) {
            throw new \Exception($errmsg);
        }

        // Try to open keepalive for tcp and disable Nagle algorithm.
        if (function_exists('socket_import_stream')) {
            if (!$socket = socket_import_stream($this->server)) {
                throw new Exception('Import stream error.');
            }

            socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
            socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
        }

        // Non blocking.
        stream_set_blocking($this->server, 0);
    }

    /**
     * Start the server.
     *
     * @throws Exception
     */
    public function start()
    {
        echo "Service starting ...\n";

        $this->daemonize();

        $this->savePid();
        $this->listen();

        Logo::display(static::VERSION, $this->ip, $this->port, self::$pid);

        static::$eventLoop = $this->eventLoop();

        static::$eventLoop->add($this->server, EventLoop::EV_READ, [$this, 'acceptConnection']);
        static::$eventLoop->loop();
    }

    /**
     * Get eventloop.
     *
     * @return Libevent|Select
     */
    protected function eventLoop()
    {
        if (extension_loaded('libevent')) {
            return new Libevent();
        }

        return new Select();
    }

    /**
     * Save pid to pid file.
     *
     * @throws Exception
     */
    protected function savePid()
    {
        self::$pid = posix_getpid();
        if (false === @file_put_contents(self::$pidFile, self::$pid)) {
            throw new Exception('can not save pid to '.self::$pidFile);
        }
    }

    /**
     * Stop the server.
     */
    public function stop()
    {
        static::$logger->info('Service stopping ...');

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

        static::$logger->info('Service starting ...');
        exec('php '.base_path('artisan').' redis-server start -d > /dev/null &');
    }

    /**
     * Accept a connection.
     *
     * @param resource $serverSocket
     *
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
     * @param int $id
     */
    public function closeConnection($id)
    {
        unset($this->connections[$id]);
    }

    /**
     * If run in daemon mode.
     *
     * @return bool
     */
    protected function isDaemonize()
    {
        return config('laredis.daemonize') || in_array('-d', $_SERVER['argv']);
    }

    /**
     * Run server in daemon mode.
     *
     * @throws Exception
     */
    protected function daemonize()
    {
        if (!$this->isDaemonize()) {
            echo "Press Ctrl-C to quit. Start success.\n";

            return;
        }

        echo "Input \"php artisan redis-server stop\" to quit. Start success.\n";

        umask(0);
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new \Exception('fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }
        if (-1 === posix_setsid()) {
            throw new \Exception('setsid fail');
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new \Exception('fork fail');
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
        $passwords = (array) config('laredis.password');

        return !empty($passwords);
    }

    /**
     * Validate password.
     *
     * @param $password
     *
     * @return bool
     */
    public static function validatePassword($password)
    {
        $passwords = (array) config('laredis.password');

        return in_array($password, $passwords);
    }

    /**
     * Show usage.
     *
     * @param $method
     * @param $arguments
     */
    public function __call($method, $arguments)
    {
        echo "Usage: php artisan {start|stop|restart}\n";
    }
}

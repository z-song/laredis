<?php

namespace Encore\Redis\Server;

use Encore\Redis\Auth\AuthManager;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use InvalidArgumentException;
use Encore\Redis\Routing\Request;
use Encore\Redis\Routing\Response;
use Encore\Redis\Exceptions\NotFoundRouteException;
use Encore\Redis\Exceptions\NotFoundCommandException;

class Connection
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var
     */
    public $socket;

    /**
     * @var Server
     */
    protected $server;

    /**
     * Remote address.
     *
     * @var string
     */
    protected $remoteAddress;

    /**
     * Remote ip.
     *
     * @var string
     */
    protected $remoteIp = '';

    /**
     * Remote port.
     *
     * @var int
     */
    protected $remotePort = 0;

    /**
     * Receive buffer.
     *
     * @var string
     */
    protected $recvBuffer = '';

    /**
     * Send buffer.
     *
     * @var string
     */
    protected $sendBuffer = '';

    /**
     * Connection counter.
     *
     * @var int
     */
    public static $counter = 1;

    /**
     * Create a new connection instance.
     *
     * @param resource $socket
     * @param string $remoteAddress
     */
    public function __construct($socket, $remoteAddress)
    {
        $this->socket = $socket;
        $this->remoteAddress = $remoteAddress;
        $this->id = static::$counter++;

        Server::log("$this->id connected");

        stream_set_blocking($this->socket, 0);
        Server::$eventLoop->add($this->socket, EventLoop::EV_READ, [$this, 'read']);
    }

    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @param $socket
     *
     * @return void
     */
    public function read($socket)
    {
        // Check connection closed.
        if (! $buffer = static::parseCommand($socket)) {
            $this->destroy();
            return;
        }

        $command = array_shift($buffer);

        try {
            $request = new Request($command, $buffer);
            $request->setConnection($this);
            $response = app('redis.router')->dispatch($request);

        } catch (NotFoundRouteException $e) {
            $response = new Response(null);
        } catch (NotFoundCommandException $e) {
            $response = new Response("unsupported command '{$e->getMessage()}'", Response::ERR);
        } catch (InvalidArgumentException $e) {
            $response = new Response("wrong number of arguments for '{$e->getMessage()}' command", Response::ERR);
        } catch (Exception $e) {
            $response = new Response($e->getMessage() . $e->getTraceAsString(), Response::ERR);
        }

        if ($response instanceof Renderable) {
            $this->sendBuffer = $response->render();

            Server::$eventLoop->add($this->socket, EventLoop::EV_WRITE, [$this, 'write']);
        }
    }

    /**
     * Base write handler.
     *
     * @return void|bool
     */
    public function write()
    {
        Server::log($this->sendBuffer);

        $len = @fwrite($this->socket, $this->sendBuffer);

        if ($len === strlen($this->sendBuffer)) {
            Server::$eventLoop->del($this->socket, EventLoop::EV_WRITE);
            $this->recvBuffer = '';

            return true;
        }

        return false;
    }

    /**
     * Destroy this connection.
     *
     * @return void
     */
    protected function destroy()
    {
        Server::log("$this->id closed");

        // Remove event listener.
        Server::$eventLoop->del($this->socket, EventLoop::EV_READ);
        Server::$eventLoop->del($this->socket, EventLoop::EV_WRITE);

        // Close socket.
        @fclose($this->socket);

        if ($this->server) {
            $this->server->closeConnection($this->id);
        }

        AuthManager::remove($this);
    }

    /**
     * Get remote IP.
     *
     * @return string
     */
    public function getRemoteIp()
    {
        if (! $this->remoteIp) {
            list($this->remoteIp, $this->remotePort) = explode(':', $this->remoteAddress, 2);
        }

        return $this->remoteIp;
    }

    /**
     * Get remote port.
     *
     * @return int
     */
    public function getRemotePort()
    {
        if (! $this->remotePort) {
            list($this->remoteIp, $this->remotePort) = explode(':', $this->remoteAddress, 2);
        }

        return $this->remotePort;
    }

    public function fingerprint()
    {
        return sha1(
            '|'.$this->getRemoteIp().
            '|'.$this->getRemotePort()
        );
    }

    /**
     * Parse command from client.
     *
     * @param $socket
     * @return array|int|string|void
     */
    public static function parseCommand($socket)
    {
        $chunk = fgets($socket);

        if ($chunk === false || $chunk === '') {
            //$this->onConnectionError('Error while reading line from the server.');
        }

        $prefix = $chunk[0];
        $payload = substr($chunk, 1, -2);

        switch ($prefix) {
            case '+':
                //return StatusResponse::get($payload);

            case '$':
                $size = (int) $payload;

                if ($size === -1) {
                    return null;
                }

                $bulkData = '';
                $bytesLeft = ($size += 2);

                do {
                    $chunk = fread($socket, min($bytesLeft, 4096));

                    if ($chunk === false || $chunk === '') {
                        //$this->onConnectionError('Error while reading bytes from the server.');
                    }

                    $bulkData .= $chunk;
                    $bytesLeft = $size - strlen($bulkData);
                } while ($bytesLeft > 0);

                return substr($bulkData, 0, -2);

            case '*':
                $count = (int) $payload;

                if ($count === -1) {
                    return null;
                }

                $multibulk = array();

                for ($i = 0; $i < $count; ++$i) {
                    $multibulk[$i] = static::parseCommand($socket);
                }

                return $multibulk;

            case ':':
                $integer = (int) $payload;
                return $integer == $payload ? $integer : $payload;

            case '-':
                //return new ErrorResponse($payload);

            default:
                //$this->onProtocolError("Unknown response prefix: '$prefix'.");

                return null;
        }
    }
}

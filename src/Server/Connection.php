<?php

namespace Encore\Redis\Server;

use Encore\Redis\Exceptions\NotFoundCommandException;
use Encore\Redis\Exceptions\NotFoundRouteException;
use Encore\Redis\Routing\Request;
use Encore\Redis\Routing\Response;
use Encore\Redis\Routing\Router;

class Connection
{
    /**
     * Read buffer size.
     *
     * @var int
     */
    const READ_BUFFER_SIZE = 65535;

    public $id;

    public $socket;

    protected $server;

    protected $remoteAddress;

    protected $recvBuffer = '';

    protected $sendBuffer = '';

    public static $counter = 1;

    public static $authenticated = [];

    public function __construct($socket, $remoteAddress)
    {
        $this->socket = $socket;
        $this->remoteAddress = $remoteAddress;
        $this->id = static::$counter++;

        Server::log("$this->id connected");

        stream_set_blocking($this->socket, 0);
        Server::$eventLoop->add($this->socket, EventLoop::EV_READ, [$this, 'read']);
    }

    public function read($socket)
    {
        $read_data = false;

        if ($buffer = $this->recvCommand($socket)) {
            $read_data = true;
        }

        // Check connection closed.
        if (!$read_data) {
            $this->destroy();
            return;
        }

        $command =  array_shift($buffer);

        try {

            $request = new Request($command, $buffer);
            $request->setConnection($this);

            $response = app('redis.router')->dispatch($request);

        } catch (NotFoundRouteException $e) {
            $response = new Response(null);
        } catch (NotFoundCommandException $e) {
            $response = new Response("unsupported command '{$e->getMessage()}'", Response::ERR);
        } catch (\Exception $e) {
            $response = new Response($e->getMessage(), Response::ERR);
        }

        $this->sendBuffer = (string) $response;

        Server::log($this->sendBuffer);

        Server::$eventLoop->add($this->socket, EventLoop::EV_WRITE, [$this, 'write']);
    }

    public function recvCommand($socket)
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
                    return;
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
                    return;
                }

                $multibulk = array();

                for ($i = 0; $i < $count; ++$i) {
                    $multibulk[$i] = $this->recvCommand($socket);
                }

                return $multibulk;

            case ':':
                $integer = (int) $payload;
                return $integer == $payload ? $integer : $payload;

            case '-':
                //return new ErrorResponse($payload);

            default:
                //$this->onProtocolError("Unknown response prefix: '$prefix'.");

                return;
        }
    }

    /**
     * Base write handler.
     *
     * @return void|bool
     */
    public function write()
    {
        $len = @fwrite($this->socket, $this->sendBuffer);


        if ($len === strlen($this->sendBuffer)) {
            Server::$eventLoop->del($this->socket, EventLoop::EV_WRITE);
            $this->clearRecvBuffer();

            return true;
        }
    }

    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    protected function destroy()
    {
        Server::log("$this->id closed");

        // Remove event listener.
        Server::$eventLoop->del($this->socket, EventLoop::EV_READ);
        Server::$eventLoop->del($this->socket, EventLoop::EV_WRITE);
        // Close socket.
        @fclose($this->socket);
        // Remove from worker->connections.
        if ($this->server) {
            unset($this->server->connections[$this->id]);
        }
    }

    protected function clearRecvBuffer()
    {
        $this->recvBuffer = '';
    }
}

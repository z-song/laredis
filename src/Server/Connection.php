<?php

namespace Encore\Laredis\Server;

use Exception;
use InvalidArgumentException;
use Encore\Laredis\Routing\Request;
use Encore\Laredis\Routing\Response;
use Illuminate\Contracts\Support\Renderable;
use Encore\Laredis\Exceptions\NotFoundRouteException;
use Encore\Laredis\Exceptions\NotFoundCommandException;

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
    public $server;

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
     * If connection is authenticated.
     *
     * @var bool
     */
    public $authenticated = false;

    /**
     * If is Multi mode.
     *
     * @var bool
     */
    public $isMulti = false;

    /**
     * Request queue.
     *
     * @var object<SplQueue>
     */
    public $requestQueue;

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

        Server::$logger->info("$this->id connected");

        stream_set_blocking($this->socket, 0);
        Server::$eventLoop->add($this->socket, EventLoop::EV_READ, [$this, 'read']);

        list($this->remoteIp, $this->remotePort) = explode(':', $this->remoteAddress, 2);
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

            $this->logRequest($request);

            if ($this->isMulti && ! $this->isTransactionCmd($command)) {
                $response = $this->queueCommand($request);
            } else {
                $response = app('redis.router')->dispatch($request);
            }

        } catch (Exception $e) {
            $response = $this->handleException($e);
        }

        if ($response instanceof Renderable) {
            $this->sendBuffer = $response->render();

            Server::$eventLoop->add($this->socket, EventLoop::EV_WRITE, [$this, 'write']);
        }
    }

    /**
     * Log the request.
     *
     * @param Request $request
     * @return void
     */
    protected function logRequest(Request $request)
    {
        $context = array_merge([$request->command()], $request->parameters());

        Server::$logger->info('CMD', $context);
    }

    /**
     * Is transaction commands.
     *
     * @param $command
     * @return bool
     */
    public function isTransactionCmd($command)
    {
        return in_array(strtoupper($command), ['MULTI', 'DISCARD', 'EXEC', 'WATCH', 'UNWATCH']);
    }

    /**
     * Queue commands.
     *
     * @param Request $request
     * @return Response
     */
    public function queueCommand(Request $request)
    {
        if (empty($this->requestQueue)) {
            $this->requestQueue = new \SplQueue();
        }

        $this->requestQueue->enqueue($request);

        return new Response('QUEUED', Response::STATUS);
    }

    /**
     * Clear multi.
     */
    public function clearMulti()
    {
        $this->isMulti = false;
        $this->requestQueue = new \SplQueue();;
    }

    /**
     * Handle exceptions.
     *
     * @param Exception $exception
     * @return Response
     */
    public function handleException(Exception $exception)
    {
        try {

            throw $exception;

        } catch (NotFoundRouteException $e) {
            $response = new Response(null);
        } catch (NotFoundCommandException $e) {
            $response = new Response(
                "unsupported command '{$e->getMessage()}'",
                Response::ERR
            );
        } catch (InvalidArgumentException $e) {
            $response = new Response(
                "wrong number of arguments for '{$e->getMessage()}' command",
                Response::ERR
            );
        } catch (Exception $e) {
            $response = new Response($e->getMessage(), Response::ERR);
        }

        if ($response->isError()) {
            echo $exception->getTraceAsString(), "\r\n";
            Server::$logger->error('ERROR:'.$exception->getMessage(), $exception->getTrace());
        }

        return $response;
    }

    /**
     * Base write handler.
     *
     * @return boolean
     */
    public function write()
    {
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
     * @throws Exception
     */
    protected function destroy()
    {
        Server::$logger->info("$this->id closed");

        // Remove event listener.
        Server::$eventLoop->del($this->socket, EventLoop::EV_READ);
        Server::$eventLoop->del($this->socket, EventLoop::EV_WRITE);

        // Close socket.
        if(! fclose($this->socket)) {
            throw new Exception('Close socket error.');
        }

        if ($this->server) {
            $this->server->closeConnection($this->id);
        }

        $this->authenticated = false;
    }

    /**
     * Get remote IP.
     *
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->remoteIp;
    }

    /**
     * Get remote port.
     *
     * @return int
     */
    public function getRemotePort()
    {
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
     * @return array|int|string
     * @throws Exception
     */
    public static function parseCommand($socket)
    {
        $chunk = fgets($socket);

        if ($chunk === false || $chunk === '') {
            throw new Exception('Error while reading line from the server.');
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
                        throw new Exception('Error while reading line from the server.');
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
                throw new Exception("Unknown response prefix: '$prefix'.");
        }
    }
}

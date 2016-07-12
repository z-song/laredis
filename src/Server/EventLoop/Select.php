<?php

namespace Encore\Laredis\Server\EventLoop;

use Encore\Laredis\Server\EventLoop;

class Select implements EventLoop
{
    /**
     * Fds waiting for read event.
     *
     * @var array
     */
    protected $readFds = [];

    /**
     * Fds waiting for write event.
     *
     * @var array
     */
    protected $writeFds = [];

    /**
     * All listeners for read/write event.
     *
     * @var array
     */
    public $allEvents = [];

    /**
     * Event listeners of signal.
     *
     * @var array
     */
    public $signalEvents = [];

    /**
     * Select timeout.
     *
     * @var int
     */
    protected $selectTimeout = 100000000;

    /**
     * {@inheritdoc}
     */
    public function add($fd, $flag, $func, $args = [])
    {
        $fdKey = (int)$fd;

        switch ($flag) {
            case self::EV_READ:
                $this->allEvents[$fdKey][$flag] = [$func, $fd];
                $this->readFds[$fdKey]          = $fd;
                break;
            case self::EV_WRITE:
                $this->allEvents[$fdKey][$flag] = [$func, $fd];
                $this->writeFds[$fdKey]         = $fd;
                break;
            case self::EV_SIGNAL:
                $this->signalEvents[$fdKey][$flag] = [$func, $fd];
                pcntl_signal($fd, [$this, 'signalHandler']);
                break;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function del($fd, $flag)
    {
        $fdKey = (int)$fd;
        switch ($flag) {
            case self::EV_READ:
                unset($this->allEvents[$fdKey][$flag], $this->readFds[$fdKey]);
                if (empty($this->allEvents[$fdKey])) {
                    unset($this->allEvents[$fdKey]);
                }
                return true;
            case self::EV_WRITE:
                unset($this->allEvents[$fdKey][$flag], $this->writeFds[$fdKey]);
                if (empty($this->allEvents[$fdKey])) {
                    unset($this->allEvents[$fdKey]);
                }
                return true;
            case self::EV_SIGNAL:
                unset($this->signalEvents[$fdKey]);
                pcntl_signal($fd, SIG_IGN);
                break;
        }
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function loop()
    {
        $e = null;
        while (1) {
            // Calls signal handlers for pending signals
            pcntl_signal_dispatch();

            $read  = $this->readFds;
            $write = $this->writeFds;
            // Waiting read/write/signal/timeout events.
            $ret = @stream_select($read, $write, $e, 0, $this->selectTimeout);

            if (!$ret) {
                continue;
            }

            foreach ($read as $fd) {
                $fdKey = (int)$fd;
                if (isset($this->allEvents[$fdKey][self::EV_READ])) {
                    call_user_func_array(
                        $this->allEvents[$fdKey][self::EV_READ][0],
                        [$this->allEvents[$fdKey][self::EV_READ][1]]
                    );
                }
            }

            foreach ($write as $fd) {
                $fdKey = (int)$fd;
                if (isset($this->allEvents[$fdKey][self::EV_WRITE])) {
                    call_user_func_array(
                        $this->allEvents[$fdKey][self::EV_WRITE][0],
                        [$this->allEvents[$fdKey][self::EV_WRITE][1]]
                    );
                }
            }
        }
    }
}

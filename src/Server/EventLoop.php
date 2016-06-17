<?php

namespace Encore\Redis\Server;

class EventLoop
{
    /**
     * Read event.
     *
     * @var int
     */
    const EV_READ = 1;

    /**
     * Write event.
     *
     * @var int
     */
    const EV_WRITE = 2;

    /**
     * Signal event.
     *
     * @var int
     */
    const EV_SIGNAL = 4;

    /**
     * Timer event.
     *
     * @var int
     */
    const EV_TIMER = 8;

    /**
     * Timer once event.
     *
     * @var int
     */
    const EV_TIMER_ONCE = 16;
    
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
    public $allEvents = array();

    /**
     * Select timeout.
     *
     * @var int
     */
    protected $selectTimeout = 100000000;

    /**
     * {@inheritdoc}
     */
    public function add($fd, $flag, $func, $args = array())
    {
        switch ($flag) {
            case self::EV_READ:
                $fdKey                           = (int)$fd;
                $this->allEvents[$fdKey][$flag] = array($func, $fd);
                $this->readFds[$fdKey]          = $fd;
                break;
            case self::EV_WRITE:
                $fdKey                           = (int)$fd;
                $this->allEvents[$fdKey][$flag] = array($func, $fd);
                $this->writeFds[$fdKey]         = $fd;
                break;
//            case self::EV_SIGNAL:
//                $fdKey                              = (int)$fd;
//                $this->_signalEvents[$fdKey][$flag] = array($func, $fd);
//                pcntl_signal($fd, array($this, 'signalHandler'));
//                break;
//            case self::EV_TIMER:
//            case self::EV_TIMER_ONCE:
//                $run_time = microtime(true) + $fd;
//                $this->_scheduler->insert($this->_timerId, -$run_time);
//                $this->_task[$this->_timerId] = array($func, (array)$args, $flag, $fd);
//                $this->tick();
//                return $this->_timerId++;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function del($fd, $flag)
    {
        $fd_key = (int)$fd;
        switch ($flag) {
            case self::EV_READ:
                unset($this->allEvents[$fd_key][$flag], $this->readFds[$fd_key]);
                if (empty($this->allEvents[$fd_key])) {
                    unset($this->allEvents[$fd_key]);
                }
                return true;
            case self::EV_WRITE:
                unset($this->allEvents[$fd_key][$flag], $this->writeFds[$fd_key]);
                if (empty($this->allEvents[$fd_key])) {
                    unset($this->allEvents[$fd_key]);
                }
                return true;
//            case self::EV_SIGNAL:
//                unset($this->_signalEvents[$fd_key]);
//                pcntl_signal($fd, SIG_IGN);
//                break;
//            case self::EV_TIMER:
//            case self::EV_TIMER_ONCE;
//                unset($this->_task[$fd_key]);
//                return true;
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

//            if (!$this->_scheduler->isEmpty()) {
//                $this->tick();
//            }

            if (!$ret) {
                continue;
            }

            foreach ($read as $fd) {
                $fdKey = (int)$fd;
                if (isset($this->allEvents[$fdKey][self::EV_READ])) {
                    call_user_func_array($this->allEvents[$fdKey][self::EV_READ][0],
                        array($this->allEvents[$fdKey][self::EV_READ][1]));
                }
            }

            foreach ($write as $fd) {
                $fdKey = (int)$fd;
                if (isset($this->allEvents[$fdKey][self::EV_WRITE])) {
                    call_user_func_array($this->allEvents[$fdKey][self::EV_WRITE][0],
                        array($this->allEvents[$fdKey][self::EV_WRITE][1]));
                }
            }
        }
    }
}
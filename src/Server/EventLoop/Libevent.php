<?php

namespace Encore\Laredis\Server\EventLoop;

use Encore\Laredis\Server\EventLoop;

/**
 * libevent eventloop
 */
class Libevent implements EventLoop
{
    /**
     * Event base.
     *
     * @var resource
     */
    protected $eventBase = null;

    /**
     * All listeners for read/write event.
     *
     * @var array
     */
    protected $allEvents = array();

    /**
     * Event listeners of signal.
     *
     * @var array
     */
    protected $eventSignal = array();

    /**
     * construct
     */
    public function __construct()
    {
        $this->eventBase = event_base_new();
    }

    /**
     * {@inheritdoc}
     */
    public function add($fd, $flag, $func, $args = array())
    {
        switch ($flag) {
            case self::EV_SIGNAL:
                $fd_key                      = (int)$fd;
                $real_flag                   = EV_SIGNAL | EV_PERSIST;
                $this->eventSignal[$fd_key] = event_new();
                if (!event_set($this->eventSignal[$fd_key], $fd, $real_flag, $func, null)) {
                    return false;
                }
                if (!event_base_set($this->eventSignal[$fd_key], $this->eventBase)) {
                    return false;
                }
                if (!event_add($this->eventSignal[$fd_key])) {
                    return false;
                }
                return true;

            default:
                $fd_key    = (int)$fd;
                $real_flag = $flag === self::EV_READ ? EV_READ | EV_PERSIST : EV_WRITE | EV_PERSIST;

                $event = event_new();

                if (!event_set($event, $fd, $real_flag, $func, null)) {
                    return false;
                }

                if (!event_base_set($event, $this->eventBase)) {
                    return false;
                }

                if (!event_add($event)) {
                    return false;
                }

                $this->allEvents[$fd_key][$flag] = $event;

                return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function del($fd, $flag)
    {
        switch ($flag) {
            case self::EV_READ:
            case self::EV_WRITE:
                $fd_key = (int)$fd;
                if (isset($this->allEvents[$fd_key][$flag])) {
                    event_del($this->allEvents[$fd_key][$flag]);
                    unset($this->allEvents[$fd_key][$flag]);
                }
                if (empty($this->allEvents[$fd_key])) {
                    unset($this->allEvents[$fd_key]);
                }
                break;
            case self::EV_SIGNAL:
                $fd_key = (int)$fd;
                if (isset($this->eventSignal[$fd_key])) {
                    event_del($this->eventSignal[$fd_key]);
                    unset($this->eventSignal[$fd_key]);
                }
                break;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function loop()
    {
        event_base_loop($this->eventBase);
    }
}

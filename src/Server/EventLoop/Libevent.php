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
        $fdKey = (int)$fd;

        switch ($flag) {
            case self::EV_SIGNAL:
                $realFlag = EV_SIGNAL | EV_PERSIST;

                $this->eventSignal[$fdKey] = event_new();

                if (!event_set($this->eventSignal[$fdKey], $fd, $realFlag, $func, null)) {
                    return false;
                }

                if (!event_base_set($this->eventSignal[$fdKey], $this->eventBase)) {
                    return false;
                }

                if (!event_add($this->eventSignal[$fdKey])) {
                    return false;
                }

                return true;

            default:
                $realFlag = $flag === self::EV_READ ? EV_READ | EV_PERSIST : EV_WRITE | EV_PERSIST;

                $event = event_new();

                if (!event_set($event, $fd, $realFlag, $func, null)) {
                    return false;
                }

                if (!event_base_set($event, $this->eventBase)) {
                    return false;
                }

                if (!event_add($event)) {
                    return false;
                }

                $this->allEvents[$fdKey][$flag] = $event;

                return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function del($fd, $flag)
    {
        $fdKey = (int)$fd;

        switch ($flag) {
            case self::EV_READ:
            case self::EV_WRITE:

                if (isset($this->allEvents[$fdKey][$flag])) {
                    event_del($this->allEvents[$fdKey][$flag]);
                    unset($this->allEvents[$fdKey][$flag]);
                }
                if (empty($this->allEvents[$fdKey])) {
                    unset($this->allEvents[$fdKey]);
                }
                break;
            case self::EV_SIGNAL:
                if (isset($this->eventSignal[$fdKey])) {
                    event_del($this->eventSignal[$fdKey]);
                    unset($this->eventSignal[$fdKey]);
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

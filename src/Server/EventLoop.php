<?php

namespace Encore\Laredis\Server;

interface EventLoop
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

    public function add($fd, $flag, $func, $args = array());

    public function del($fd, $flag);

    public function loop();
}

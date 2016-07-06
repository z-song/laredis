<?php

namespace Encore\Laredis\Command;

class ServerTime extends Command
{
    protected $name = 'TIME';

    public function process()
    {
        list($microseconds, $timestamp) = explode(' ', microtime());

        return [(string)$timestamp, (string) ($microseconds * 1000000)];
    }
}

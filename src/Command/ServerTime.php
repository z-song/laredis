<?php

namespace Encore\Redis\Command;

class ServerTime extends Command
{
    protected $name = 'TIME';

    public function execute()
    {
        list($microseconds, $timestamp) = explode(' ', microtime());

        return [(string)$timestamp, (string) ($microseconds * 1000000)];
    }
}

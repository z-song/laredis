<?php

namespace Encore\Redis\Command;

class ServerTime extends Command
{
    public function execute()
    {
        list($microseconds, $timestamp) = explode(' ', microtime());

        return [(string)$timestamp, (string) ($microseconds * 1000000)];
    }
}
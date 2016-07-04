<?php

namespace Encore\Redis\Command;

class ConnectionPing extends Command
{
    protected $name = 'PING';

    public function execute()
    {
        return 'PONG';
    }
}

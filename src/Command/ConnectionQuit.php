<?php

namespace Encore\Redis\Command;

class ConnectionQuit extends Command
{
    protected $name = 'QUIT';

    public function execute()
    {
        return true;
    }
}

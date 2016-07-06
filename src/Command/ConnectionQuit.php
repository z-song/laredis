<?php

namespace Encore\Laredis\Command;

class ConnectionQuit extends Command
{
    protected $name = 'QUIT';

    public function process()
    {
        return true;
    }
}

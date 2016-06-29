<?php

namespace Encore\Redis\Command;

class ConnectionPing extends Command
{
    public function execute()
    {
        return 'PONG';
    }
}

<?php

namespace Encore\Redis\Command;

class ConnectionEcho extends Command
{
    public function execute()
    {
        return (string) $this->arguments[0];
    }
}

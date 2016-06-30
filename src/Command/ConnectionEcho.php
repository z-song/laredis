<?php

namespace Encore\Redis\Command;

class ConnectionEcho extends Command
{
    protected $argumentCount = 1;

    public function execute()
    {
        return (string) $this->arguments[0];
    }
}

<?php

namespace Encore\Redis\Command;

class ConnectionEcho extends Command
{
    protected $name = 'ECHO';

    protected $arity = 1;

    public function execute()
    {
        return (string) $this->arguments[0];
    }
}

<?php

namespace Encore\Laredis\Command;

class ConnectionEcho extends Command
{
    protected $name = 'ECHO';

    protected $arity = 1;

    public function process()
    {
        return (string) $this->arguments[0];
    }
}

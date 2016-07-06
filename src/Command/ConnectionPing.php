<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Response;

class ConnectionPing extends Command
{
    protected $name = 'PING';

    public function process()
    {
        return new Response('PONG', Response::STATUS);
    }
}

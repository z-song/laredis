<?php

namespace Encore\Redis\Command;

use Encore\Redis\Exceptions\AuthException;
use Encore\Redis\Server\Server;

class ConnectionAuth extends Command
{
    protected $name = 'AUTH';

    protected $arity = 1;

    public function execute()
    {
        if (! Server::requirePass()) {
            throw new AuthException('Client sent AUTH, but no password is set');
        }

        if (! Server::validatePassword($this->arguments[0])) {
            throw new AuthException('invalid password');
        }

        $this->request->connection()->authenticated = true;

        return true;
    }
}

<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Exceptions\AuthException;
use Encore\Laredis\Server\Server;

class ConnectionAuth extends Command
{
    protected $name = 'AUTH';

    protected $arity = 1;

    public function process()
    {
        if (!Server::requirePass()) {
            throw new AuthException('Client sent AUTH, but no password is set');
        }

        if (!Server::validatePassword($this->arguments[0])) {
            throw new AuthException('invalid password');
        }

        $this->request->connection()->authenticated = true;

        return true;
    }
}

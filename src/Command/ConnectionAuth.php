<?php

namespace Encore\Redis\Command;

use Encore\Redis\Auth\AuthManager;
use Encore\Redis\Exceptions\AuthException;

class ConnectionAuth extends Command
{
    protected $argumentCount = 1;

    public function execute()
    {
        if (! $this->validatePassword($this->arguments[0])) {
            throw new AuthException('invalid password');
        }

        AuthManager::authorize($this->request->connection());

        return true;
    }

    protected function validatePassword($password)
    {
        $passwords  = (array) config('redis-server.password');

        if (empty($passwords)) {
            throw new AuthException('Client sent AUTH, but no password is set');
        }

        return in_array($password, $passwords);
    }
}

<?php

namespace Encore\Redis\Command;

use Encore\Redis\Auth\Container;
use Encore\Redis\Exceptions\AuthException;

class ConnectionAuth extends Command
{
    public function execute()
    {
        if (! $this->validatePassword($this->arguments[0])) {
            throw new AuthException('密码错误');
        }

        Container::add($this->request->connection()->id);

        return true;
    }

    protected function validatePassword($password)
    {
        return in_array($password, (array) config('redis-server.password'));
    }
}

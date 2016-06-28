<?php

namespace Encore\Redis\Controllers;

use Encore\Redis\Exceptions\AuthException;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function handle($password)
    {
        if (! $this->validatePassword($password)) {
            throw new AuthException('密码错误');
        }

        return true;
    }

    protected function validatePassword($password)
    {
        return in_array($password, (array) config('redis-server.password'));
    }
}

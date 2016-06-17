<?php

namespace Encore\Redis\Controllers;

use Encore\Redis\Exceptions\AuthException;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function handle($password)
    {
        if ($password !== '123') {
            throw new AuthException('密码错误');
        }

        return true;
    }
}

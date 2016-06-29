<?php

namespace Encore\Redis\DataType;

use Encore\Redis\Exceptions\NotFoundCommandException;

class Server implements DataType
{
    use DataTypeTrait;

    public function client()
    {
        return true;
    }

    public function time()
    {
        list($microseconds, $timestamp) = explode(' ', microtime());

        return [(string)$timestamp, (string) ($microseconds * 1000000)];
    }

    public function __call($method, $arguments)
    {
        throw new NotFoundCommandException($method);
    }
}

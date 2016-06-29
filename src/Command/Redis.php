<?php

namespace Encore\Redis\Command;

use Encore\Redis\Exceptions\NotFoundCommandException;

class Redis
{
    public static function supportedCommands()
    {
        return [
            'AUTH'  => ConnectionAuth::class,
            'ECHO'  => ConnectionEcho::class,
            'PING'  => ConnectionPing::class,
            //'QUIT'  => ConnectionQuit::class,
            //'SELECT'=> ConnectionSelect::class,

            'TIME'  => ServerTime::class,
        ];
    }

    public static function supports($command)
    {
        return array_key_exists(strtoupper($command), static::supportedCommands());
    }

    public static function findCommandClass($command)
    {
        if (! static::supports($command)) {
            throw new NotFoundCommandException();
        }

        return static::supportedCommands()[$command];
    }
}

<?php

namespace Encore\Redis\Command;

use Predis\Command\ConnectionAuth;
use Predis\Command\ConnectionEcho;
use Predis\Command\ConnectionPing;
use Predis\Command\ConnectionQuit;
use Predis\Command\ConnectionSelect;

class Commands
{
    public static function supportedCommands()
    {
        return [
            'AUTH'  => ConnectionAuth::class,
            'ECHO'  => ConnectionEcho::class,
            'PING'  => ConnectionPing::class,
            'QUIT'  => ConnectionQuit::class,
            'SELECT'=> ConnectionSelect::class,
        ];
    }

    public static function supports($command)
    {
        return array_key_exists(strtoupper($command), static::supportedCommands());
    }
}

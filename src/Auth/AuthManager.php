<?php

namespace Encore\Redis\Auth;

use Encore\Redis\Server\Connection;

class AuthManager
{
    public static $pool = [];

    public static function authorize(Connection $connection)
    {
        static::$pool[$connection->fingerprint()] = $connection->id;
    }

    public static function check(Connection $connection)
    {
        return isset(static::$pool[$connection->fingerprint()]) &&
            static::$pool[$connection->fingerprint()] === $connection->id;
    }

    public static function remove(Connection $connection)
    {
        if (array_key_exists($connection->fingerprint(), static::$pool)) {
            unset(static::$pool[$connection->fingerprint()]);
        }
    }
}

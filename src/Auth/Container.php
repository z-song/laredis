<?php

namespace Encore\Redis\Auth;

class Container
{
    public static $authenticated = [];

    public static function add($id)
    {
        static::$authenticated[] = $id;

        static::$authenticated = array_unique(static::$authenticated);
    }

    public static function has($id)
    {
        return in_array($id, static::$authenticated);
    }

    public static function all()
    {
        return static::$authenticated;
    }
}

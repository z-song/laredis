<?php

namespace Encore\Redis\DataType;

class Commands
{
    public static $commands = [
        'hash' => [
            'HDEL', 'HEXISTS', 'HGET', 'HGETALL', 'HINCRBY', 'HINCRBYFLOAT', 'HKEYS',
            'HLEN', 'HMGET', 'HMSET', 'HSET', 'HSETNX', 'HVALS', 'HSCAN',
        ],

        'list' => [
            'BLPOP', 'BRPOP', 'BRPOPLPUSH', 'LINDEX', 'LINSERT', 'LLEN', 'LPOP', 'LPUSH',
            'LPUSHX', 'LRANGE', 'LREM', 'LSET', 'LTRIM', 'RPOP', 'RPOPLPUSH', 'RPUSH', 'RPUSHX',
        ],

        'string' => [
            'APPEND', 'BITCOUNT', 'BITOP', 'DECR', 'DECRBY', 'GET', 'GETBIT', 'GETRANGE',
            'GETSET', 'INCR', 'INCRBY', 'INCRBYFLOAT', 'MGET', 'MSET', 'MSETNX',
            'PSETEX', 'SET', 'SETBIT', 'SETEX', 'SETNX', 'SETRANGE', 'STRLEN',
        ],
    ];

    public static function hash()
    {
        return static::$commands['hash'];
    }

    public static function rlist()
    {
        return static::$commands['list'];
    }

    public static function string()
    {
        return static::$commands['string'];
    }

    public static function all()
    {
        return array_flatten(static::$commands);
    }

    public static function has($command)
    {
        return in_array(strtoupper($command), static::all());
    }
}

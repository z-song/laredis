<?php

namespace Encore\Redis\Command;

use Encore\Redis\Exceptions\NotFoundCommandException;

class Redis
{
    /**
     * Map for command to class the the server supported.
     *
     * @return array
     */
    public static function supportedCommands()
    {
        return [
            'AUTH'      => ConnectionAuth::class,
            'ECHO'      => ConnectionEcho::class,
            'PING'      => ConnectionPing::class,
            'QUIT'      => ConnectionQuit::class,
            'SELECT'    => ConnectionSelect::class,

            'TIME'      => ServerTime::class,

            'GET'       => StringGet::class,
            'SET'       => StringSet::class,
            'GETSET'    => StringGetSet::class,
            'STRLEN'    => StringStrlen::class,
            'MGET'      => StringMget::class,
            'MSET'      => StringMset::class,

            'DEL'       => KeyDel::class,
            'EXISTS'    => KeyExists::class,
            //'KEYS'      => KeyKeys::class,

            'HGET'      => HashHget::class,
            'HSET'      => HashHset::class,
            'HGETALL'   => HashHgetall::class,
            'HVALS'     => HashHvals::class,
            'HKEYS'     => HashHkeys::class,
            'HDEL'      => HashHdel::class,
            'HLEN'      => HashHlen::class,
            'HMGET'     => HashHmget::class,
            'HMSET'     => HashHmset::class,

            'LINDEX'    => ListLindex::class,
            'LRANGE'    => ListLrange::class,
            'LLEN'      => ListLlen::class,

            'PUBLISH'   => PubSubPublish::class,
            'SUBSCRIBE' => PubSubSubscribe::class,

            'SADD'      => SetSadd::class,
            'SCARD'     => SetScard::class,
            'SDIFF'     => SetSdiff::class,
            'SISMEMBER' => SetSismember::class,
            'SMEMBERS'  => SetSmembers::class,
            'SREM'      => SetSrem::class,
        ];
    }

    /**
     * Check the server supports given command.
     *
     * @param string $command
     * @return bool
     */
    public static function supports($command)
    {
        return array_key_exists(strtoupper($command), static::supportedCommands());
    }

    /**
     * Find command class by command name.
     *
     * @param string $command
     * @return mixed
     * @throws NotFoundCommandException
     */
    public static function findCommandClass($command)
    {
        if (! static::supports($command)) {
            throw new NotFoundCommandException();
        }

        return static::supportedCommands()[$command];
    }
}

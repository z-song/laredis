<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Exceptions\NotFoundCommandException;

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
            'MGET'      => StringGetMultiple::class,
            'MSET'      => StringSetMultiple::class,

            'DEL'       => KeyDelete::class,

            'HGET'      => HashGet::class,
            'HSET'      => HashSet::class,
            'HGETALL'   => HashGetAll::class,
            'HVALS'     => HashValues::class,
            'HKEYS'     => HashKeys::class,
            'HDEL'      => HashDelete::class,
            'HLEN'      => HashLength::class,
            'HMGET'     => HashGetMultiple::class,
            'HMSET'     => HashSetMultiple::class,

            'LINDEX'    => ListIndex::class,
            'LRANGE'    => ListRange::class,
            'LLEN'      => ListLength::class,

            'PUBLISH'   => PubSubPublish::class,
            'SUBSCRIBE' => PubSubSubscribe::class,

            'SADD'      => SetAdd::class,
            'SCARD'     => SetCard::class,
            'SDIFF'     => SetDifference::class,
            'SISMEMBER' => SetIsMember::class,
            'SMEMBERS'  => SetMembers::class,
            'SREM'      => SetRemove::class,
            'SRANDMEMBER' => SetRandomMember::class,

            'MULTI'     => TransactionMulti::class,
            'EXEC'      => TransactionExec::class,
            'DISCARD'   => TransactionDiscard::class,
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

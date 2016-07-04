<?php

namespace Encore\Redis\Command;

class HashHset extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HSET';

    protected $arity = 3;
}

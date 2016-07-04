<?php

namespace Encore\Redis\Command;

class HashHgetall extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HGETALL';

    protected $arity = 1;
}

<?php

namespace Encore\Redis\Command;

class HashHget extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HGET';

    protected $arity = 2;
}

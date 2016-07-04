<?php

namespace Encore\Redis\Command;

class HashHlen extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HLEN';

    protected $arity = 1;
}

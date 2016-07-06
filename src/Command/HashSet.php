<?php

namespace Encore\Laredis\Command;

class HashSet extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HSET';

    protected $arity = 3;
}

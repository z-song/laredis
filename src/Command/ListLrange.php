<?php

namespace Encore\Redis\Command;

class ListLrange extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'LRANGE';

    protected $arity = 3;
}

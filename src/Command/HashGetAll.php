<?php

namespace Encore\Laredis\Command;

class HashGetAll extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HGETALL';

    protected $arity = 1;
}

<?php

namespace Encore\Redis\Command;

class SetSrem extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SREM';

    protected $arity = -2;
}

<?php

namespace Encore\Redis\Command;

class SetSismember extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SISMEMBER';

    protected $arity = 2;
}

<?php

namespace Encore\Redis\Command;

class SetSmembers extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SMEMBERS';

    protected $arity = 1;
}

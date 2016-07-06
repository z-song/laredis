<?php

namespace Encore\Laredis\Command;

class SetAdd extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SADD';

    protected $arity = -2;
}

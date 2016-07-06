<?php

namespace Encore\Laredis\Command;

class SetDifference extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SDIFF';

    protected $arity = -1;
}

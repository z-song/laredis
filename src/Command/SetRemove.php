<?php

namespace Encore\Laredis\Command;

class SetRemove extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SREM';

    protected $arity = -2;
}

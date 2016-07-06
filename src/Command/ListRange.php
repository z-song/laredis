<?php

namespace Encore\Laredis\Command;

class ListRange extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'LRANGE';

    protected $arity = 3;
}

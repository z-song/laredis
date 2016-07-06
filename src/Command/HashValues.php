<?php

namespace Encore\Laredis\Command;

class HashValues extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HVALS';

    protected $arity = 1;
}

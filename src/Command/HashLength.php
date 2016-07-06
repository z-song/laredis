<?php

namespace Encore\Laredis\Command;

class HashLength extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HLEN';

    protected $arity = 1;
}

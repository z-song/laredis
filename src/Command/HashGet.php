<?php

namespace Encore\Laredis\Command;

class HashGet extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HGET';

    protected $arity = 2;
}

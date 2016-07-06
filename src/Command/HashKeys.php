<?php

namespace Encore\Laredis\Command;

class HashKeys extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HKEYS';

    protected $arity = 1;
}

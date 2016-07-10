<?php

namespace Encore\Laredis\Command;

class KeyKeys extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'KEYS';

    protected $arity = 1;
}

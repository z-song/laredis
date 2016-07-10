<?php

namespace Encore\Laredis\Command;

class KeyExists extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'EXISTS';

    protected $arity = 1;
}

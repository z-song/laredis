<?php

namespace Encore\Laredis\Command;

class ListIndex extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'LINDEX';

    protected $arity = 2;
}

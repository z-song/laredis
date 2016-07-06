<?php

namespace Encore\Laredis\Command;

class SetCard extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SCARD';

    protected $arity = 1;
}

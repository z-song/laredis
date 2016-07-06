<?php

namespace Encore\Laredis\Command;

class ListLength extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'LLEN';

    protected $arity = 1;
}

<?php

namespace Encore\Redis\Command;

class ListLlen extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'LLEN';

    protected $arity = 1;
}

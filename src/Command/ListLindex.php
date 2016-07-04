<?php

namespace Encore\Redis\Command;

class ListLindex extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'LINDEX';

    protected $arity = 2;
}

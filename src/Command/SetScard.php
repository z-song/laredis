<?php

namespace Encore\Redis\Command;

class SetScard extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SCARD';

    protected $arity = 1;
}

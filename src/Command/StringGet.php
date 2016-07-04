<?php

namespace Encore\Redis\Command;

class StringGet extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'GET';

    protected $arity = 1;
}

<?php

namespace Encore\Redis\Command;

class HashHvals extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HVALS';

    protected $arity = 1;
}

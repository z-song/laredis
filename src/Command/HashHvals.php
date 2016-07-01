<?php

namespace Encore\Redis\Command;

class HashHvals extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 1;
}

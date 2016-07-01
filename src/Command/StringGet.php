<?php

namespace Encore\Redis\Command;

class StringGet extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 1;
}

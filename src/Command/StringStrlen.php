<?php

namespace Encore\Redis\Command;

class StringStrlen extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 1;
}

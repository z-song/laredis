<?php

namespace Encore\Redis\Command;

class ListLrange extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 3;
}

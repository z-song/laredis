<?php

namespace Encore\Redis\Command;

class ListLindex extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 2;
}

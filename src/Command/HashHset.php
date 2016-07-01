<?php

namespace Encore\Redis\Command;

class HashHset extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 3;
}

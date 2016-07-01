<?php

namespace Encore\Redis\Command;

class HashHgetall extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 1;
}

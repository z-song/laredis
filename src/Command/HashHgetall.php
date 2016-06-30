<?php

namespace Encore\Redis\Command;

class HashHgetall extends Command
{
    use RoutableTrait;

    protected $argumentCount = 1;
}
<?php

namespace Encore\Redis\Command;

class HashHget extends Command
{
    use RoutableTrait;

    protected $argumentCount = 2;
}
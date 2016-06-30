<?php

namespace Encore\Redis\Command;

class HashHset extends Command
{
    use RoutableTrait;

    protected $argumentCount = 3;
}
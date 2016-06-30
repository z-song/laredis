<?php

namespace Encore\Redis\Command;

class HashHvals extends Command
{
    use RoutableTrait;

    protected $argumentCount = 1;
}
<?php

namespace Encore\Redis\Command;

class StringGet extends Command
{
    use RoutableTrait;

    protected $argumentCount = 1;
}
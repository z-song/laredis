<?php

namespace Encore\Redis\Command;

class StringStrlen extends Command
{
    use RoutableTrait;

    protected $argumentCount = 1;
}
<?php

namespace Encore\Redis\Command;

class ListLlen extends Command
{
    use RoutableTrait;

    protected $argumentCount = 1;
}
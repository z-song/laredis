<?php

namespace Encore\Redis\Command;

class ListLrange extends Command
{
    use RoutableTrait;

    protected $argumentCount = 3;
}
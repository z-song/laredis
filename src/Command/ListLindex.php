<?php

namespace Encore\Redis\Command;

class ListLindex extends Command
{
    use RoutableTrait;

    protected $argumentCount = 2;
}
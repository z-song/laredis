<?php

namespace Encore\Redis\Command;

class HashHkeys extends Command
{
    use RoutableTrait;

    protected $argumentCount = 1;
}
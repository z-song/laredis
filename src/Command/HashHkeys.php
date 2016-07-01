<?php

namespace Encore\Redis\Command;

class HashHkeys extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 1;
}

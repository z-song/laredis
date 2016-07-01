<?php

namespace Encore\Redis\Command;

class PubSubPublish extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 2;
}

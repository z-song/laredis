<?php

namespace Encore\Redis\Command;

class PubSubPublish extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'PUBLISH';

    protected $arity = 2;
}

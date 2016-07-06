<?php

namespace Encore\Laredis\Command;

class PubSubPublish extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'PUBLISH';

    protected $arity = 2;
}

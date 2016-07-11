<?php

namespace Encore\Laredis\Command;

class PubSubSubscribe extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SUBSCRIBE';

    protected $arity = 1;
}

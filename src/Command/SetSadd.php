<?php

namespace Encore\Redis\Command;

class SetSadd extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SADD';

    protected $arity = -2;
}

<?php

namespace Encore\Laredis\Command;

class SetIsMember extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SISMEMBER';

    protected $arity = 2;
}

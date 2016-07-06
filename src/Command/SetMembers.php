<?php

namespace Encore\Laredis\Command;

class SetMembers extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SMEMBERS';

    protected $arity = 1;
}

<?php

namespace Encore\Laredis\Command;

class SetRandomMember extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SRANDMEMBER';

    protected $arity = -1;
}

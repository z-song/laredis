<?php

namespace Encore\Redis\Command;

class SetSdiff extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SDIFF';

    protected $arity = -1;
}

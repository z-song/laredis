<?php

namespace Encore\Laredis\Command;

class StringSet extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SET';

    protected $arity = -2;

    protected function validateArguments()
    {
        return parent::validateArguments() && count($this->arguments) % 2 !== 0;
    }
}

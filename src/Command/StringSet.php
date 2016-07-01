<?php

namespace Encore\Redis\Command;

class StringSet extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected function validateArguments()
    {
        return count($this->arguments) > 1;
    }
}

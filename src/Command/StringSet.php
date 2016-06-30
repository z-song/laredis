<?php

namespace Encore\Redis\Command;

class StringSet extends Command
{
    use RoutableTrait;

    protected function validateArguments()
    {
        return count($this->arguments) > 1;
    }
}
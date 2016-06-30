<?php

namespace Encore\Redis\Command;

class StringGetSet extends Command
{
    use RoutableTrait {
        execute as traitExecute;
    }

    protected $argumentCount = 2;

    public function execute()
    {
        $key = $this->arguments[0];
        $getRequest = clone $this->request;
        $getRequest->command('GET');
        $getRequest->parameters([$key]);
        $get = $this->traitExecute($getRequest);

        $value = $this->arguments[1];
        $setRequest = clone $this->request;
        $setRequest->command('SET');
        $setRequest->parameters([$key, $value]);
        $this->traitExecute($setRequest);

        return $get;
    }
}
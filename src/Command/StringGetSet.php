<?php

namespace Encore\Laredis\Command;

class StringGetSet extends Command implements RoutableInterface
{
    use RoutableTrait {
        execute as traitExecute;
    }

    protected $name = 'GETSET';

    protected $arity = 2;

    public function process()
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

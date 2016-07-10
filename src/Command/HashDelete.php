<?php

namespace Encore\Laredis\Command;

class HashDelete extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    }

    protected $name = 'HDEL';

    protected $arity = -1;

    public function process()
    {
        $parameters = [$this->arguments[0], array_slice($this->arguments, 1)];

        $this->request->setParameters($parameters);

        return $this->traitProcess();
    }
}

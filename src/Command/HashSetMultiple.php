<?php

namespace Encore\Laredis\Command;

class HashSetMultiple extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    }

    protected $name = 'HMSET';

    protected $key = '';

    protected $sets = [];

    protected $arity = -3;

    protected function validateArguments()
    {
        return parent::validateArguments() && $this->arguments % 2 !== 0;
    }

    public function process()
    {
        $this->key = $this->arguments[0];

        foreach (array_chunk(array_slice($this->arguments, 1), 2) as $pair) {
            list($key, $value) = $pair;
            $this->sets[$key] = $value;
        }

        $this->request->parameters([$this->key, $this->sets]);
        $route = $this->router->findRoute($this->request, false);

        if (is_null($route)) {
            return $this->runBatchHset();
        }

        return $this->router->runRouteWithinStack($route, $this->request);
    }

    protected function runBatchHset()
    {
        foreach ($this->sets as $key => $val) {

            $request = clone $this->request;
            $request->command('HSET');
            $request->parameters([$this->key, $key, $val]);

            $this->traitProcess($request)->value();
        }

        return true;
    }
}

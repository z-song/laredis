<?php

namespace Encore\Redis\Command;

class HashHmset extends Command implements RoutableInterface
{
    use RoutableTrait {
        execute as traitExecute;
    }

    protected $key = '';

    protected $sets = [];

    protected function validateArguments()
    {
        return count($this->arguments) > 1;
    }

    public function execute()
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

            $this->traitExecute($request)->value();
        }

        return true;
    }
}

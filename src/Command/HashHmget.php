<?php

namespace Encore\Redis\Command;

class HashHmget extends Command
{
    use RoutableTrait {
        execute as traitExecute;
    }

    protected function validateArguments()
    {
        return count($this->arguments) > 1;
    }

    public function execute()
    {
        $parameters = [$this->arguments[0], array_slice($this->arguments, 1)];

        $this->request->parameters($parameters);

        $route = $this->router->findRoute($this->request, false);

        if (is_null($route)) {
            return $this->runBatchHget();
        }

        return $this->router->runRouteWithinStack($route, $this->request);
    }

    protected function runBatchHget()
    {
        $key = $this->arguments[0];
        $result = [];

        foreach ($this->request->parameter(1) as $field) {

            $request = clone $this->request;
            $request->command('HGET');
            $request->parameters([$key, $field]);

            $result[] = $this->traitExecute($request)->value();
        }

        return $result;
    }
}
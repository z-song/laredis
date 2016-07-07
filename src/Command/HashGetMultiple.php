<?php

namespace Encore\Laredis\Command;

class HashGetMultiple extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    }

    protected $name = 'HMGET';

    protected $arity = -2;

    public function process()
    {
        $parameters = [$this->arguments[0], array_slice($this->arguments, 1)];

        $this->request->parameters($parameters);

        $route = $this->router->findRoute($this->request, false);

        if (is_null($route)) {
            return $this->getMultiple();
        }

        return $this->router->runRouteWithinStack($route, $this->request);
    }

    protected function getMultiple()
    {
        $key = $this->arguments[0];
        $result = [];

        foreach ($this->request->parameter(1) as $field) {

            $request = clone $this->request;
            $request->command('HGET');
            $request->parameters([$key, $field]);

            $result[] = $this->traitProcess($request)->value();
        }

        return $result;
    }
}

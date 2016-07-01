<?php

namespace Encore\Redis\Command;

class HashHdel extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected function validateArguments()
    {
        return count($this->arguments) > 1;
    }

    public function execute()
    {
        $parameters = [$this->arguments[0], array_slice($this->arguments, 1)];

        $this->request->parameters($parameters);

        $route = $this->router->findRoute($this->request);

        return $this->router->runRouteWithinStack($route, $this->request);
    }
}

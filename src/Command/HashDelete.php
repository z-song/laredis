<?php

namespace Encore\Laredis\Command;

class HashDelete extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'HDEL';

    protected $arity = -1;

    public function process()
    {
        $parameters = [$this->arguments[0], array_slice($this->arguments, 1)];

        $this->request->parameters($parameters);

        $route = $this->router->findRoute($this->request);

        return $this->router->runRouteWithinStack($route, $this->request);
    }
}

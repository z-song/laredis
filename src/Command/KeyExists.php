<?php

namespace Encore\Laredis\Command;

class KeyExists extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'EXISTS';

    protected $arity = 1;

    public function process()
    {
        $routes = $this->router->routes();

        foreach (array_flatten($routes) as $route) {
            if ($route->matches($this->request)) {
                return 1;
            }
        }

        return 0;
    }
}

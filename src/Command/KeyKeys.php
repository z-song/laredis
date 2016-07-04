<?php

namespace Encore\Redis\Command;

class KeyKeys extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'KEYS';

    protected $arity = 1;

    public function execute()
    {
        $routes = $this->router->routes();
        $matched = [];

        foreach (array_flatten($routes) as $route) {
            if ($route->matches($this->request)) {
                $matched[] = $route->getPath();
            }
        }

        return $matched;
    }
}

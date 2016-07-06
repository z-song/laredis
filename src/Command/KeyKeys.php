<?php

namespace Encore\Laredis\Command;

class KeyKeys extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'KEYS';

    protected $arity = 1;

    public function process()
    {
        $routes = $this->router->routes();
        $matched = [];

        foreach (array_flatten($routes) as $route) {
            if ($route->matches($this->request)) {
                $matched[] = $route->key();
            }
        }

        return $matched;
    }
}

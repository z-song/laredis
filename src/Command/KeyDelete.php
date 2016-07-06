<?php

namespace Encore\Laredis\Command;

class KeyDelete extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'DEL';

    protected $arity = -1;

    public function process()
    {
        $success = 0;

        foreach ($this->request->parameters() as $key) {

            $request = clone $this->request;
            $request->parameters([$key]);

            if ($this->runDelete($request)) {
                $success++;
            }
        }

        return $success;
    }

    protected function runDelete($request)
    {
        $route = $this->router->findRoute($request, false);

        if (is_null($route)) {
            return false;
        }

        $result = $this->router->runRouteWithinStack($route, $request);

        return (bool) $result->value();
    }
}

<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Response;

class StringGetMultiple extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'MGET';

    protected $arity = -1;

    public function process()
    {
        $result = [];

        foreach ($this->request->parameters() as $key) {

            $request = clone $this->request;
            $request->command('GET');
            $request->parameters([$key]);

            $result[] = $this->runGet($request);
        }

        return $result;
    }

    protected function runGet($request)
    {
        $route = $this->router->findRoute($request, false);

        if (is_null($route)) {
            return null;
        }

        $response = $this->router->runRouteWithinStack($route, $request);

        if ($response instanceof Response) {
            return $response->value();
        }

        return $response;
    }
}

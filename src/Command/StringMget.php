<?php

namespace Encore\Redis\Command;

use Encore\Redis\Routing\Response;

class StringMget extends Command
{
    use RoutableTrait;

    protected function validateArguments()
    {
        return count($this->arguments) > 0;
    }

    public function execute()
    {
        $result = [];

        foreach($this->request->parameters() as $key) {

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
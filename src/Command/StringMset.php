<?php

namespace Encore\Redis\Command;

class StringMset extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'MSET';

    protected $arity = -2;

    protected function validateArguments()
    {
        return parent::validateArguments() && count($this->arguments)%2 !== 0 ;
    }

    public function execute()
    {
        $chunks = array_chunk($this->request->parameters(), 2);

        foreach ($chunks as $chunk) {

            $request = clone $this->request;
            $request->command('SET');
            $request->parameters($chunk);

            $this->runSet($request);
        }

        return true;
    }

    protected function runSet($request)
    {
        $route = $this->router->findRoute($request, false);

        if (is_null($route)) {
            return null;
        }

        $this->router->runRouteWithinStack($route, $request);
    }
}

<?php

namespace Encore\Redis\Command;

class KeyDel extends Command
{
    use RoutableTrait;

    protected function validateArguments()
    {
        return count($this->arguments) > 0;
    }

    public function execute()
    {
        $success = 0;

        foreach($this->request->parameters() as $key) {

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
<?php

namespace Encore\Laredis\Routing\Laravel;

use ReflectionClass;
use ReflectionMethod;
use Encore\Laredis\Command\Redis;

class ControllerInspector
{
    /**
     * Get the routable methods for a controller.
     *
     * @param $controller
     * @return array
     */
    public function getRoutable($controller)
    {
        $routable = [];

        $reflection = new ReflectionClass($controller);

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($this->isRoutable($method)) {
                $data = $this->getMethodData($method);

                $routable[$method->name][] = $data;
            }
        }

        return $routable;
    }

    /**
     * Determine if the given controller method is routable.
     *
     * @param  \ReflectionMethod  $method
     * @return bool
     */
    public function isRoutable(ReflectionMethod $method)
    {
        if ($method->class == 'Illuminate\Routing\Controller') {
            return false;
        }

        return Redis::supports($method->name);
    }

    public function getMethodData($method)
    {
        return [
            'command'   => $method->name,
            'uri'       => $method->name
        ];
    }
}

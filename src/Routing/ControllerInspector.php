<?php

namespace Encore\Redis\Routing;

use ReflectionClass;
use ReflectionMethod;

class ControllerInspector
{
    protected $dataType;

    public function __construct($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * Get the routable methods for a controller.
     *
     * @param  string  $controller
     * @param  string  $prefix
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

        return  in_array(strtoupper($method->name), $this->dataType->commands());
    }

    public function getMethodData($method)
    {
        return [
            'command'   => $method->name,
            'uri'       => $method->name
        ];
    }
}


<?php

namespace Encore\Laredis\Routing;

class Controller
{
    /**
     * The middleware defined on the controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Define a middleware on the controller.
     *
     * @param string $middleware
     * @param array  $options
     *
     * @return void
     */
    public function middleware($middleware, array $options = [])
    {
        $this->middleware[$middleware] = $options;
    }

    /**
     * Get the middleware for a given method.
     *
     * @param string $command
     *
     * @return array
     */
    public function getMiddlewareForCommand($command)
    {
        $middleware = [];

        foreach ($this->middleware as $name => $options) {
            if (isset($options['only']) && !in_array($command, (array) $options['only'])) {
                continue;
            }

            if (isset($options['except']) && in_array($command, (array) $options['except'])) {
                continue;
            }

            $middleware[] = $name;
        }

        return $middleware;
    }
}

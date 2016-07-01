<?php

namespace Encore\Redis\Command;

use Encore\Redis\Routing\Router;

trait RoutableTrait
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param null $request
     * @return mixed
     * @throws \Encore\Redis\Exceptions\NotFoundRouteException
     */
    public function execute($request = null)
    {
        $request = $request ?: $this->request;

        $route = $this->router->findRoute($request);

        return $this->router->runRouteWithinStack($route, $request);
    }
}

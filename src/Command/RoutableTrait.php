<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Router;

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
     * @throws \Encore\Laredis\Exceptions\NotFoundRouteException
     */
    public function process($request = null)
    {
        $request = $request ?: $this->request;

        $route = $this->router->findRoute($request);

        return $this->router->runRouteWithinStack($route, $request);
    }
}

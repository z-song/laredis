<?php

namespace Encore\Laredis\Command;

trait RoutableTrait
{
    /**
     * @var
     */
    protected $router;

    /**
     * @param $router
     */
    public function setRouter($router)
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

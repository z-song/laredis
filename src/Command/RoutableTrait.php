<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\RouterInterface;

trait RoutableTrait
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param \Encore\Laredis\Routing\Request|null $request
     * @return \Encore\Laredis\Routing\Response
     */
    public function process($request = null)
    {
        $request = $request ?: $this->request;

        return $this->router->send($request, true);
    }
}

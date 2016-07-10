<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\RouterInterface;

interface RoutableInterface
{
    /**
     * @param RouterInterface $router
     * @return mixed
     */
    public function setRouter(RouterInterface $router);

    public function process();
}

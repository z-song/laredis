<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Router;

interface RoutableInterface
{
    public function setRouter(Router $router);

    public function process();
}

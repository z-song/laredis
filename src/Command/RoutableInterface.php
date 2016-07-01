<?php

namespace Encore\Redis\Command;

use Encore\Redis\Routing\Router;

interface RoutableInterface
{
    public function setRouter(Router $router);

    public function execute();
}

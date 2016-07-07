<?php

namespace Encore\Laredis\Command;

interface RoutableInterface
{
    public function setRouter($router);

    public function process();
}

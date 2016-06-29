<?php

namespace Encore\Redis\Command;

use Encore\Redis\Routing\Request;

abstract class Command
{
    protected $arguments = [];

    protected $request;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function execute()
    {
        return true;
    }
}
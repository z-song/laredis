<?php

namespace Encore\Redis\Command;

use Encore\Redis\Routing\Request;

abstract class Command
{
    protected $arguments = [];

    protected $request;

    protected $argumentCount = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->arguments = $request->parameters();

        if (! $this->validateArguments()) {
            throw new \InvalidArgumentException(strtolower($request->command()));
        }
    }

    protected function validateArguments()
    {
        return count($this->arguments) == $this->argumentCount;
    }

    public function execute()
    {
        return true;
    }

    public function routable()
    {
        return false;
    }
}
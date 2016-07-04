<?php

namespace Encore\Redis\Command;

use InvalidArgumentException;
use Encore\Redis\Routing\Request;

abstract class Command
{
    protected $name = '';

    protected $arguments = [];

    protected $request;

    protected $arity = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->arguments = $request->parameters();

        if (! $this->validateArguments()) {
            throw new InvalidArgumentException(
                strtolower($request->command())
            );
        }
    }

    protected function validateArguments()
    {
        if (($this->arity > 0 && $this->arity != count($this->arguments)) ||
            count($this->arguments) < -$this->arity
        ) {
            return false;
        }

        return true;
    }

    public function execute()
    {
        return true;
    }

    public function name()
    {
        return $this->name;
    }
}

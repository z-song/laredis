<?php

namespace Encore\Laredis\Command;

use InvalidArgumentException;
use Encore\Laredis\Routing\Request;

abstract class Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Arguments of command.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * Arity of parameters.
     *
     * @var int
     */
    protected $arity = 0;

    /**
     * Create new command instance.
     *
     * Command constructor.
     * @param Request $request
     */
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

    /**
     * Validate arguments.
     *
     * @return bool
     */
    protected function validateArguments()
    {
        if (($this->arity > 0 && $this->arity != count($this->arguments)) ||
            count($this->arguments) < -$this->arity
        ) {
            return false;
        }

        return true;
    }

    /**
     * Process the command.
     *
     * @return bool
     */
    public function process()
    {
        return true;
    }

    /**
     * Get name of the command.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }
}

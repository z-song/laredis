<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Response;

class TransactionExec extends Command implements RoutableInterface
{
    use RoutableTrait {
        execute as traitExecute;
    }

    protected $name = 'EXEC';

    public function process()
    {
        if (! $this->request->connection()->isMulti) {
            return new Response('EXEC without MULTI', Response::ERR);
        }

        foreach ($this->request->connection()->requestQueue as $request) {
            $result[] = $this->traitExecute($request)->value();
        }

        $this->request->connection()->clearMulti();

        return $result;
    }
}

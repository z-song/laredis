<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Response;

class TransactionDiscard extends Command
{
    protected $name = 'DISCARD';

    public function process()
    {
        if (!$this->request->connection()->isMulti) {
            return new Response('DISCARD without MULTI', Response::ERR);
        }

        $this->request->connection()->clearMulti();

        return true;
    }
}

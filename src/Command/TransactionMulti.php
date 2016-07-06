<?php

namespace Encore\Laredis\Command;

class TransactionMulti extends Command
{
    protected $name = 'MULTI';

    public function process()
    {
        $this->request->connection()->isMulti = true;

        return true;
    }
}

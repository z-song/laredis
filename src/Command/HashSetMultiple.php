<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Request;

class HashSetMultiple extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    }

    protected $name = 'HMSET';

    protected $key = '';

    protected $arity = -3;

    protected function validateArguments()
    {
        return parent::validateArguments() && $this->arguments % 2 !== 0;
    }

    public function process()
    {
        $this->key = $this->arguments[0];

        foreach (array_chunk(array_slice($this->arguments, 1), 2) as $pair) {
            list($field, $value) = $pair;
            $request = new Request('HSET', [$this->key, $field, $value]);
            $request->setConnection($this->request->connection());
            $this->traitProcess($request);
        }

        return true;
    }
}

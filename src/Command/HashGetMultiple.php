<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Request;

class HashGetMultiple extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    }

    protected $name = 'HMGET';

    protected $arity = -2;

    public function process()
    {
        $result = [];

        foreach (array_slice($this->arguments, 1) as $field) {
            $request = new Request('HGET', [$this->arguments[0], $field]);
            $request->setConnection($this->request->connection());
            try {
                $value = $this->traitProcess($request)->value();
            } catch (\Exception $e) {
                $value = null;
            }

            $result[] = $value;
        }

        return $result;
    }
}

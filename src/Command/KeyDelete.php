<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Request;

class KeyDelete extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    };

    protected $name = 'DEL';

    protected $arity = -1;

    public function process()
    {
        $success = 0;

        foreach ($this->request->parameters() as $key) {

            $request = new Request('DEL', [$key]);
            $request->setConnection($this->request->connection());
            if ($this->traitProcess($request)->value()) {
                $success++;
            }
        }

        return $success;
    }
}

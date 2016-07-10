<?php

namespace Encore\Laredis\Command;

class StringGetMultiple extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    }

    protected $name = 'MGET';

    protected $arity = -1;

    public function process()
    {
        $result = [];

        foreach ($this->request->parameters() as $key) {

            $request = clone $this->request;
            $request->setCommand('GET');
            $request->setParameters([$key]);

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

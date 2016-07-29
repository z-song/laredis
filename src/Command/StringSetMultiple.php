<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Request;

class StringSetMultiple extends Command implements RoutableInterface
{
    use RoutableTrait {
        process as traitProcess;
    }

    protected $name = 'MSET';

    protected $arity = -2;

    protected function validateArguments()
    {
        return parent::validateArguments() && count($this->arguments) % 2 === 0;
    }

    public function process()
    {
        $chunks = array_chunk($this->request->parameters(), 2);

        foreach ($chunks as $chunk) {
            $request = new Request('SET', $chunk);
            $request->setConnection($this->request->connection());
            try {
                $this->traitProcess($request);
            } catch (\Exception $e) {
                // Pass if catch a exception.
            }
        }

        return true;
    }
}

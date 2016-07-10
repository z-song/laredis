<?php

namespace Encore\Laredis\Command;

use Encore\Laredis\Routing\Request;

class StringGetSet extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'GETSET';

    protected $arity = 2;

    public function process()
    {
        $key   = $this->arguments[0];
        $value = $this->arguments[1];

        $request = new Request('GET', [$key]);
        $request->setConnection($this->request->connection());
        $retval = $this->router->send($request);

        $request = new Request('SET', [$key, $value]);
        $request->setConnection($this->request->connection());
        $this->router->send($request);

        return $retval;
    }
}

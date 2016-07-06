<?php

namespace Encore\Laredis\Command;

class PubSubSubscribe extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $name = 'SUBSCRIBE';

    protected $arity = 1;

//    public function process()
//    {
//        return function () {
//            for ($i = 1; $i <= 10; $i++) {
//
//                sleep(1);
//
//                $message = ['message', 'chnannel', $i];
//                $response = new Response($message);
//
//                yield $response->render();
//            }
//        };
//    }
}

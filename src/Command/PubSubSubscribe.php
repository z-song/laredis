<?php

namespace Encore\Redis\Command;

class PubSubSubscribe extends Command implements RoutableInterface
{
    use RoutableTrait;

    protected $argumentCount = 1;

//    public function execute()
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

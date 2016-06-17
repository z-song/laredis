<?php

return [

    'auth' => [
        'guard' => 'config',
    ],

    /*'guards' => [
        'config' => [
            'driver' =>
        ],
        'users' =>
    ],*/

    'password'  => [
        '123456'
    ],

    'middleware' => [
        'redis.auth' => Encore\Redis\Middleware\Authenticate::class,
    ]
];
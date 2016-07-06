<?php

return [

    'daemonize' => false,

    'password'  => [
        '123456'
    ],

    'logFile' => storage_path('redis-server.log'),

    'middleware' => [
        'redis.auth' => Encore\Laredis\Middleware\Authenticate::class,
    ]
];
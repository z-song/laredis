<?php

return [

    'daemonize' => env('LAREDIS_DAEMONIZE', false),

    'password'  => [
        env('LAREDIS_PASSWORD', '123456'),
    ],

    'logFile' => storage_path('/logs/redis-server.log'),

    'middleware' => [
        'redis.auth' => Encore\Laredis\Middleware\Authenticate::class,
    ],
];

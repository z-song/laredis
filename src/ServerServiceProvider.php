<?php

namespace Encore\Laredis;

use Encore\Laredis\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ServerServiceProvider extends ServiceProvider
{
    protected $commands = [
        'ServeCommand'
    ];

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes([__DIR__ . '/../config/laredis.php' => config_path('laredis.php'),], 'laredis');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (empty(config('laredis'))) {
            $config = require __DIR__ . '/../config/laredis.php';
            config(['laredis' => $config]);
        }

        $this->registerRouter();
        $this->registerCommands();
    }

    /**
     * Register the router.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('redis.router', function ($app) {
            return new Router($app);
        });
    }

    /**
     * Register the commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        foreach ($this->commands as $command) {
            $this->commands('Encore\Laredis\Console\\' . $command);
        }
    }
}

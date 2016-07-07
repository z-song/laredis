<?php

namespace Encore\Laredis;

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
        $this->app['redis.router'] = $this->app->share(function ($app) {

            $routerClass = $this->getRouterClass();

            return new $routerClass($app);
        });
    }

    public function getRouterClass()
    {
        if (str_contains($this->app->version(), 'Lumen')) {
            return \Encore\Laredis\Routing\Lumen\Router::class;
        }

        return \Encore\Laredis\Routing\Laravel\Router::class;
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

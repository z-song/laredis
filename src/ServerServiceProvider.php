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
        } else {
            $config = require __DIR__ . '/../config/laredis.php';dd($config);
            config(['laredis' => $config]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
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

            $router = new Router($app);

            foreach ((array) config('laredis.middleware') as $name => $class) {
                $router->middleware($name, $class);
            }

            return $router;
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

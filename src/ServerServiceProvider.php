<?php

namespace Encore\Redis;

use Encore\Redis\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ServerServiceProvider extends ServiceProvider
{
    protected $commands = [
        'ServeCommand'
    ];

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

            foreach (config('redis-server.middleware') as $name => $class) {
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
            $this->commands('Encore\Redis\Console\\' . $command);
        }
    }
}

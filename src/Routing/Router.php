<?php

namespace Encore\Redis\Routing;

use Closure;
use Encore\Redis\Command\Commands;
use Encore\Redis\Command\Redis;
use Illuminate\Container\Container;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Arr;
use Encore\Redis\Exceptions\NotFoundRouteException;
use Encore\Redis\Exceptions\NotFoundCommandException;

class Router
{
    /**
     * The route collection instance.
     *
     * @var array
     */
    protected $routes;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Routable data types.
     *
     * @var array
     */
    protected $routableDataTypes = ['string', 'hash', 'list'];

    /**
     * Create a new Router instance.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: new Container;
    }

    public function addControllerRoute($uri, $controller)
    {
        if (! empty($this->groupStack)) {
            $prepended = $this->prependGroupUses($controller);
        }

        $routable = (new ControllerInspector())->getRoutable($prepended);

        foreach ($routable as $command => $routes) {
            foreach ($routes as $route) {
                $this->__call($command, [$uri, ['uses' => "$controller@$command"]]);
            }
        }
    }

    protected function addRoute($command, $uri, $action)
    {
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }

        $command = strtoupper($command);

        $route = new Route($command, $uri, $action);
        $route->setRouter($this)->setContainer($this->container);

        if ($this->hasGroupStack()) {
            $this->mergeGroupAttributesIntoRoute($route);
        }

        $this->routes[$command][] = $route;
    }

    public function routes()
    {
        return $this->routes;
    }

    /**
     * Merge the group stack with the controller action.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     */
    protected function mergeGroupAttributesIntoRoute($route)
    {
        $action = $this->mergeWithLastGroup($route->getAction());

        $route->setAction($action);
    }

    /**
     * Determine if the action is routing to a controller.
     *
     * @param  array  $action
     * @return bool
     */
    protected function actionReferencesController($action)
    {
        if ($action instanceof Closure) {
            return false;
        }

        return is_string($action) || is_string(isset($action['uses']) ? $action['uses'] : null);
    }

    /**
     * Add a controller based route action to the action array.
     *
     * @param  array|string  $action
     * @return array
     */
    protected function convertToControllerAction($action)
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        // Here we'll merge any group "uses" statement if necessary so that the action
        // has the proper clause for this property. Then we can simply set the name
        // of the controller on the action and return the action array for usage.
        if (! empty($this->groupStack)) {
            $action['uses'] = $this->prependGroupUses($action['uses']);
        }

        // Here we will set this controller name on the action array just so we always
        // have a copy of it for reference if we need it. This can be used while we
        // search for a controller name or do some other type of fetch operation.
        $action['controller'] = $action['uses'];

        return $action;
    }

    /**
     * Prepend the last group uses onto the use clause.
     *
     * @param  string  $uses
     * @return string
     */
    protected function prependGroupUses($uses)
    {
        $group = end($this->groupStack);

        return isset($group['namespace']) && strpos($uses, '\\') !== 0 ? $group['namespace'] . '\\' . $uses : $uses;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  array     $attributes
     * @param  \Closure  $callback
     * @return void
     */
    public function group(array $attributes, Closure $callback)
    {
        $this->updateGroupStack($attributes);

        // Once we have updated the group stack, we will execute the user Closure and
        // merge in the groups attributes when the route is created. After we have
        // run the callback, we will pop the attributes off of this group stack.
        call_user_func($callback, $this);

        array_pop($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param  array  $attributes
     * @return void
     */
    protected function updateGroupStack(array $attributes)
    {
        if (! empty($this->groupStack)) {
            $attributes = $this->mergeGroup($attributes, end($this->groupStack));
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param  array  $new
     * @return array
     */
    public function mergeWithLastGroup($new)
    {
        return $this->mergeGroup($new, end($this->groupStack));
    }

    /**
     * Merge the given group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return array
     */
    public static function mergeGroup($new, $old)
    {
        $new['namespace'] = static::formatUsesPrefix($new, $old);

        $new['prefix'] = static::formatGroupPrefix($new, $old);

        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        $new['where'] = array_merge(
            isset($old['where']) ? $old['where'] : [],
            isset($new['where']) ? $new['where'] : []
        );

        if (isset($old['as'])) {
            $new['as'] = $old['as'].(isset($new['as']) ? $new['as'] : '');
        }

        return array_merge_recursive(Arr::except($old, ['namespace', 'prefix', 'where', 'as']), $new);
    }

    /**
     * Format the uses prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    protected static function formatUsesPrefix($new, $old)
    {
        if (isset($new['namespace'])) {
            return isset($old['namespace'])
                ? trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\')
                : trim($new['namespace'], '\\');
        }

        return isset($old['namespace']) ? $old['namespace'] : null;
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     * @return string|null
     */
    protected static function formatGroupPrefix($new, $old)
    {
        $oldPrefix = isset($old['prefix']) ? $old['prefix'] : null;

        if (isset($new['prefix'])) {
            return trim($oldPrefix, '/').'/'.trim($new['prefix'], '/');
        }

        return $oldPrefix;
    }

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }

    /**
     * Get the current group stack for the router.
     *
     * @return array
     */
    public function getGroupStack()
    {
        return $this->groupStack;
    }

    public function dispatch(Request $request)
    {
        if (! Redis::supports($request->command())) {
            throw new NotFoundCommandException($request->command());
        }

        $commandClass = Redis::findCommandClass($request->command());

        $command = new $commandClass($request);

        if ($command->routable()) {
            $command->setRouter($this);
        }

        return $this->prepareResponse($request, $command->execute());
    }

    /**
     * Run the given route within a Stack "onion" instance.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function runRouteWithinStack(Route $route, Request $request)
    {
        $shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
            $this->container->make('middleware.disable') === true;

        $middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddlewares($route);

        return (new Pipeline($this->container))
            ->send($request)
            ->through($middleware)
            ->then(function ($request) use ($route) {
                return $this->prepareResponse(
                    $request,
                    $route->run($request)
                );
            });
    }

    /**
     * Gather the middleware for the given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    public function gatherRouteMiddlewares(Route $route)
    {
        return collect($route->middleware())->map(function ($name) {
            return collect($this->resolveMiddlewareClassName($name));
        })->flatten()->all();
    }

    /**
     * Resolve the middleware name to a class name(s) preserving passed parameters.
     *
     * @param  string  $name
     * @return string|array
     */
    public function resolveMiddlewareClassName($name)
    {
        $map = $this->middleware;

        // If the middleware is the name of a middleware group, we will return the array
        // of middlewares that belong to the group. This allows developers to group a
        // set of middleware under single keys that can be conveniently referenced.
        if (isset($this->middlewareGroups[$name])) {
            return $this->parseMiddlewareGroup($name);

            // When the middleware is simply a Closure, we will return this Closure instance
            // directly so that Closures can be registered as middleware inline, which is
            // convenient on occasions when the developers are experimenting with them.
        } elseif (isset($map[$name]) && $map[$name] instanceof Closure) {
            return $map[$name];

            // Finally, when the middleware is simply a string mapped to a class name the
            // middleware name will get parsed into the full class name and parameters
            // which may be run using the Pipeline which accepts this string format.
        } else {
            list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

            return (isset($map[$name]) ? $map[$name] : $name).
            ($parameters !== null ? ':'.$parameters : '');
        }
    }

    /**
     * Parse the middleware group and format it for usage.
     *
     * @param  string  $name
     * @return array
     */
    protected function parseMiddlewareGroup($name)
    {
        $results = [];

        foreach ($this->middlewareGroups[$name] as $middleware) {
            // If the middleware is another middleware group we will pull in the group and
            // merge its middleware into the results. This allows groups to conveniently
            // reference other groups without needing to repeat all their middlewares.
            if (isset($this->middlewareGroups[$middleware])) {
                $results = array_merge(
                    $results, $this->parseMiddlewareGroup($middleware)
                );

                continue;
            }

            list($middleware, $parameters) = array_pad(
                explode(':', $middleware, 2), 2, null
            );

            // If this middleware is actually a route middleware, we will extract the full
            // class name out of the middleware list now. Then we'll add the parameters
            // back onto this class' name so the pipeline will properly extract them.
            if (isset($this->middleware[$middleware])) {
                $middleware = $this->middleware[$middleware];
            }

            $results[] = $middleware.($parameters ? ':'.$parameters : '');
        }

        return $results;
    }

    /**
     * Get all of the defined middleware short-hand names.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Register a short-hand name for a middleware.
     *
     * @param  string  $name
     * @param  string  $class
     * @return $this
     */
    public function middleware($name, $class)
    {
        $this->middleware[$name] = $class;

        return $this;
    }

    /**
     * @param Request $request
     * @param bool $throwException
     * @return mixed
     * @throws NotFoundCommandException
     * @throws NotFoundRouteException
     */
    public function findRoute(Request $request, $throwException = true)
    {
        if (! array_key_exists($request->command(), $this->routes) && $throwException) {
            throw new NotFoundRouteException($request->command());
        }

        $routesForCommand = array_get($this->routes, $request->command());

        foreach ((array) $routesForCommand as $route) {
            if ($route->matches($request)) {
                return $route->bind($request);
            }
        }

        if ($throwException) {
            throw new NotFoundRouteException($request->path());
        }
    }

    public function __call($method, $arguments)
    {
        if (in_array($method, $this->routableDataTypes)) {
            return call_user_func_array([$this, 'addControllerRoute'], $arguments);
        }

        if (Redis::supports($method)) {
            $this->addRoute($method, $arguments[0], $arguments[1]);
        }
    }

    public function prepareResponse(Request $request, $response)
    {
        if (! $response instanceof Response) {
            $response = new Response($response);
        }

        return $response;
    }
}

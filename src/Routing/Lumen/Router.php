<?php

namespace Encore\Laredis\Routing\Lumen;

use Closure;
use Encore\Laredis\Routing\RouterInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Encore\Laredis\Command\Redis;
use Illuminate\Container\Container;
use Laravel\Lumen\Routing\Pipeline;
use Encore\Laredis\Routing\Request;
use Encore\Laredis\Routing\Response;
use Encore\Laredis\Command\RoutableInterface;
use Laravel\Lumen\Routing\Closure as RoutingClosure;
use Encore\Laredis\Exceptions\NotFoundRouteException;
use Encore\Laredis\Exceptions\NotFoundCommandException;
use Laravel\Lumen\Routing\Controller as LumenController;

class Router implements RouterInterface
{
    /**
     * All of the routes waiting to be registered.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * All of the named routes and URI pairs.
     *
     * @var array
     */
    public $namedRoutes = [];

    /**
     * All of the global middleware for the application.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the route specific middleware short-hands.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * The shared attributes for the current route group.
     *
     * @var array|null
     */
    protected $groupAttributes;

    /**
     * The current route being dispatched.
     *
     * @var array
     */
    protected $currentRoute;

    /**
     * The FastRoute dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Create a new Router instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: new Container;

        $this->loadMiddleware();
    }

    /**
     * Load middleware.
     *
     * @return void
     */
    public function loadMiddleware()
    {
        $this->routeMiddleware((array) config('laredis.middleware'));
    }

    /**
     * Register a set of routes with a set of shared attributes.
     *
     * @param  array  $attributes
     * @param  \Closure  $callback
     * @return void
     */
    public function group(array $attributes, Closure $callback)
    {
        $parentGroupAttributes = $this->groupAttributes;

        if (isset($attributes['middleware']) && is_string($attributes['middleware'])) {
            $attributes['middleware'] = explode('|', $attributes['middleware']);
        }

        $this->groupAttributes = $attributes;

        call_user_func($callback, $this);

        $this->groupAttributes = $parentGroupAttributes;
    }

    /**
     * Add command.
     *
     * @param $command
     * @param $key
     * @param $action
     * @return $this
     */
    public function command($command, $key, $action)
    {
        $this->addRoute($command, $key, $action);

        return $this;
    }

    /**
     * Add a route to the collection.
     *
     * @param string $command
     * @param string $key
     * @param mixed $action
     */
    public function addRoute($command, $key, $action)
    {
        $action = $this->parseAction($action);

        if (isset($this->groupAttributes)) {
            if (isset($this->groupAttributes['prefix'])) {
                $key = trim($this->groupAttributes['prefix'], '/').'/'.trim($key, '/');
            }

            if (isset($this->groupAttributes['suffix'])) {
                $key = trim($key, '/').rtrim($this->groupAttributes['suffix'], '/');
            }

            $action = $this->mergeGroupAttributes($action);
        }

        $key = '/'.trim($key, '/');

        if (isset($action['as'])) {
            $this->namedRoutes[$action['as']] = $key;
        }

        $this->routes[$command.$key] = ['method' => strtoupper($command), 'uri' => $key, 'action' => $action];
    }

    /**
     * Parse the action into an array format.
     *
     * @param  mixed  $action
     * @return array
     */
    protected function parseAction($action)
    {
        if (is_string($action)) {
            return ['uses' => $action];
        } elseif (! is_array($action)) {
            return [$action];
        }

        if (isset($action['middleware']) && is_string($action['middleware'])) {
            $action['middleware'] = explode('|', $action['middleware']);
        }

        return $action;
    }

    /**
     * Merge the group attributes into the action.
     *
     * @param  array  $action
     * @return array
     */
    protected function mergeGroupAttributes(array $action)
    {
        return $this->mergeNamespaceGroup(
            $this->mergeMiddlewareGroup($action)
        );
    }

    /**
     * Merge the namespace group into the action.
     *
     * @param  array  $action
     * @return array
     */
    protected function mergeNamespaceGroup(array $action)
    {
        if (isset($this->groupAttributes['namespace']) && isset($action['uses'])) {
            $action['uses'] = $this->groupAttributes['namespace'].'\\'.$action['uses'];
        }

        return $action;
    }

    /**
     * Merge the middleware group into the action.
     *
     * @param  array  $action
     * @return array
     */
    protected function mergeMiddlewareGroup($action)
    {
        if (isset($this->groupAttributes['middleware'])) {
            if (isset($action['middleware'])) {
                $action['middleware'] = array_merge($this->groupAttributes['middleware'], $action['middleware']);
            } else {
                $action['middleware'] = $this->groupAttributes['middleware'];
            }
        }

        return $action;
    }

    /**
     * Add new middleware to the application.
     *
     * @param  Closure|array  $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        if (! is_array($middleware)) {
            $middleware = [$middleware];
        }

        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }

    /**
     * Define the route middleware for the application.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function routeMiddleware(array $middleware)
    {
        $this->routeMiddleware = array_merge($this->routeMiddleware, $middleware);

        return $this;
    }

    /**
     * Call the terminable middleware.
     *
     * @param  mixed  $response
     * @return void
     */
    protected function callTerminableMiddleware($response)
    {
        $response = $this->prepareResponse($response);

        foreach ($this->middleware as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            $instance = $this->container->make($middleware);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($this->request, $response);
            }
        }
    }

    /**
     * Dispatch the incoming request.
     *
     * @param Request $request
     * @return Response
     * @throws NotFoundCommandException
     */
    public function dispatch(Request $request)
    {
        if (! Redis::supports($request->command())) {
            throw new NotFoundCommandException($request->command());
        }

        $commandClass = Redis::findCommandClass($request->command());

        $command = new $commandClass($request);

        if ($command instanceof RoutableInterface) {
            $command->setRouter($this);
        }

        return $this->prepareResponse($command->process());
    }

    /**
     * Send request.
     *
     * @param Request $request
     * @return mixed
     */
    public function send(Request $request)
    {
        $this->request = $request;

        return $this->sendThroughPipeline($this->middleware, function () use ($request) {
            if (isset($this->routes[$request->command().$request->key()])) {
                return $this->handleFoundRoute(
                    [true, $this->routes[$request->command().$request->key()]['action'], []]
                );
            }

            return $this->handleDispatcherResponse(
                $this->createDispatcher()->dispatch($request->command(), '/'.$request->key())
            );
        });
    }

    /**
     * Parse the incoming request and return the command and key.
     *
     * @param Request $request
     * @return string[]
     */
    protected function parseIncomingRequest($request)
    {
        $this->container->instance('Encore\Laredis\Routing\Request', $request);

        return [$request->command(), $request->key()];
    }

    /**
     * Create a FastRoute dispatcher instance for the application.
     *
     * @return Dispatcher
     */
    protected function createDispatcher()
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });
    }

    /**
     * Set the FastRoute dispatcher instance.
     *
     * @param  \FastRoute\Dispatcher  $dispatcher
     * @return void
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the response from the FastRoute dispatcher.
     *
     * @param array $routeInfo
     * @return Response|null
     * @throws NotFoundRouteException
     */
    protected function handleDispatcherResponse($routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundRouteException;

            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new NotFoundRouteException();

            case Dispatcher::FOUND:
                return $this->handleFoundRoute($routeInfo);
        }

        return null;
    }

    /**
     * Handle a route found by the dispatcher.
     *
     * @param  array  $routeInfo
     * @return Response
     */
    protected function handleFoundRoute($routeInfo)
    {
        $this->currentRoute = $routeInfo;

        $action = $routeInfo[1];

        // Pipe through route middleware...
        if (isset($action['middleware'])) {
            $middleware = $this->gatherMiddlewareClassNames($action['middleware']);

            return $this->prepareResponse($this->sendThroughPipeline($middleware, function () use ($routeInfo) {
                return $this->callActionOnArrayBasedRoute($routeInfo);
            }));
        }

        return $this->prepareResponse(
            $this->callActionOnArrayBasedRoute($routeInfo)
        );
    }

    /**
     * Call the Closure on the array based route.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callActionOnArrayBasedRoute($routeInfo)
    {
        $action = $routeInfo[1];

        if (isset($action['uses'])) {
            return $this->prepareResponse($this->callControllerAction($routeInfo));
        }

        $closure = null;

        foreach ($action as $value) {
            if ($value instanceof Closure) {
                $closure = $value->bindTo(new RoutingClosure);
                break;
            }
        }

        return $this->prepareResponse($this->container->call($closure, $routeInfo[2]));
    }

    /**
     * Call a controller based route.
     *
     * @param array $routeInfo
     * @return Response|mixed
     * @throws NotFoundRouteException
     */
    protected function callControllerAction($routeInfo)
    {
        list($controller, $method) = explode('@', $routeInfo[1]['uses']);

        if (! method_exists($instance = $this->container->make($controller), $method)) {
            throw new NotFoundRouteException;
        }

        if ($instance instanceof LumenController) {
            return $this->callLumenController($instance, $method, $routeInfo);
        } else {
            return $this->callControllerCallable(
                [$instance, $method],
                $routeInfo[2]
            );
        }
    }

    /**
     * Send the request through a Lumen controller.
     *
     * @param  mixed  $instance
     * @param  string  $method
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callLumenController($instance, $method, $routeInfo)
    {
        $middleware = $instance->getMiddlewareForMethod($method);

        if (count($middleware) > 0) {
            return $this->callLumenControllerWithMiddleware(
                $instance,
                $method,
                $routeInfo,
                $middleware
            );
        } else {
            return $this->callControllerCallable(
                [$instance, $method],
                $routeInfo[2]
            );
        }
    }

    /**
     * Send the request through a set of controller middleware.
     *
     * @param  mixed  $instance
     * @param  string  $method
     * @param  array  $routeInfo
     * @param  array  $middleware
     * @return mixed
     */
    protected function callLumenControllerWithMiddleware($instance, $method, $routeInfo, $middleware)
    {
        $middleware = $this->gatherMiddlewareClassNames($middleware);

        return $this->sendThroughPipeline($middleware, function () use ($instance, $method, $routeInfo) {
            return $this->callControllerCallable([$instance, $method], $routeInfo[2]);
        });
    }

    /**
     * Call a controller callable and return the response.
     *
     * @param  callable  $callable
     * @param  array  $parameters
     * @return \Encore\Laredis\Routing\Response
     */
    protected function callControllerCallable(callable $callable, array $parameters = [])
    {
        $parameters = array_merge($parameters, array_slice($this->request->parameters(), 1));

        return $this->prepareResponse(
            $this->container->call($callable, $parameters)
        );
    }

    /**
     * Gather the full class names for the middleware short-cut string.
     *
     * @param  string|array  $middleware
     * @return array
     */
    protected function gatherMiddlewareClassNames($middleware)
    {
        $middleware = is_string($middleware) ? explode('|', $middleware) : (array) $middleware;

        return array_map(function ($name) {
            list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

            return array_get($this->routeMiddleware, $name, $name).($parameters ? ':'.$parameters : '');
        }, $middleware);
    }

    /**
     * Send the request through the pipeline with the given callback.
     *
     * @param  array  $middleware
     * @param  \Closure  $then
     * @return mixed
     */
    protected function sendThroughPipeline(array $middleware, Closure $then)
    {
        $shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
            $this->container->make('middleware.disable') === true;

        if (count($middleware) > 0 && ! $shouldSkipMiddleware) {
            return (new Pipeline($this->container))
                ->send($this->request)
                ->through($middleware)
                ->then($then);
        }

        return $then();
    }

    /**
     * Prepares for the response.
     *
     * @param mixed $response
     * @return Response
     */
    public function prepareResponse($response)
    {
        if ($response instanceof Response) {
            return $response;
        }

        return new Response($response);
    }

    /**
     * Get the raw routes for the application.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    public function __call($method, $arguments)
    {
        if (Redis::supports($method)) {
            $this->addRoute($method, $arguments[0], $arguments[1]);
        }
    }
}

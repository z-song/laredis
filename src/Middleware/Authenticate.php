<?php

namespace Encore\Redis\Middleware;

use Closure;
use Encore\Redis\Exceptions\AuthException;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Encore\Redis\Routing\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->authenticated()) {
            return $next($request);
        }

        throw new AuthException('Unauthorized');
    }
}

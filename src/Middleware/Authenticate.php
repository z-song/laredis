<?php

namespace Encore\Redis\Middleware;

use Closure;
use Encore\Redis\Routing\Request;
use Encore\Redis\Exceptions\AuthException;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     * @throws AuthException
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->authenticated() || $this->passwordNotSet()) {
            return $next($request);
        }

        throw new AuthException('Unauthorized');
    }

    protected function passwordNotSet()
    {
        $passwords = (array) config('redis-server.password');

        return empty($passwords);
    }
}

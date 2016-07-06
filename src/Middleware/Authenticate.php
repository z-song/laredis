<?php

namespace Encore\Laredis\Middleware;

use Closure;
use Encore\Laredis\Server\Server;
use Encore\Laredis\Routing\Request;
use Encore\Laredis\Exceptions\AuthException;

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
        if (! Server::requirePass() || $request->connection()->authenticated) {
            return $next($request);
        }

        throw new AuthException('Unauthorized');
    }
}

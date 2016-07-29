<?php

namespace Encore\Laredis\Middleware;

use Closure;
use Encore\Laredis\Exceptions\AuthException;
use Encore\Laredis\Routing\Request;
use Encore\Laredis\Server\Server;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @throws AuthException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Server::requirePass() || $request->connection()->authenticated) {
            return $next($request);
        }

        throw new AuthException('Unauthorized');
    }
}

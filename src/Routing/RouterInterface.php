<?php

namespace Encore\Laredis\Routing;

interface RouterInterface
{
    /**
     * Send request.
     *
     * @param Request $request
     * @return mixed
     */
    public function send(Request $request);

    public function dispatch(Request $request);

    public function prepareResponse($response);
}

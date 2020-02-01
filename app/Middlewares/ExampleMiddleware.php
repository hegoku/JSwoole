<?php
namespace App\Middlewares;

use JSwoole\Response;

class ExampleMiddleware
{
    public function handle($request, $next)
    {
        if ($request->get('a')!='') {
            return $next($request);
        } else {
            return (new Response())->withHeader('Content-Type', 'application/json')->withStatus(200)->withBody(json_encode([
                'code'=>400,
                'msg'=>'Missing query a'
            ]));
        }
    }
}
<?php
namespace App\Middlewares;

use JSwoole\Response;

class Example2Middleware
{
    public function handle($request, $next)
    {
        if ($request->get('b')!='') {
            return $next($request);
        } else {
            return (new Response())->withHeader('Content-Type', 'application/json')->withStatus(200)->withBody(json_encode([
                'code'=>400,
                'msg'=>'Missing query b'
            ]));
        }
    }
}
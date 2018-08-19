<?php
$server=new swoole_http_server('127.0.0.1', 2222, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

$server->set([
    'reactor_num' => 1,
    'worker_num' => 1,
    'max_request' => 1000,
    'daemonize' => 0,
    'dispatch_mode' => 3,
    'reload_async' => true,
]);

$server->on('WorkerStart', function(swoole_server $server, int $worker_id){
    if(function_exists('apc_clear_cache')){
        apc_clear_cache();
    }
    if(function_exists('opcache_reset')){
        opcache_reset();
    }

    require_once('vendor/autoload.php');

    $env_file = parse_ini_file(__DIR__.'/env');
    foreach ($env_file as $k=>$v) {
        putenv($k.'='.$v);
    }

    $app_config=require_once('config/app.php');
    \JSwoole\JSwoole::initWorderContext($worker_id, $app_config);
});
$server->on('WorkerStop', function(swoole_server $server, int $worker_id){
    
});
$server->on('request', function($swooleRequest, $swooleResponse){
    if ($swooleRequest->server['request_uri']=='/favicon.ico') {
        return $swooleResponse->end('');
    }
    \JSwoole\JSwoole::addRequestContext();
    try {
        // \Swoole\Runtime::enableCoroutime();
    
        $route=new \JSwoole\Route\Route();
        $route->loadRouter(\JSwoole\JSwoole::getWorkerContext()->getConfig('route'));
        $controller='';
        $action='';
        try {
            list($controller, $action)=$route->parseUri($swooleRequest->server['request_method'], $swooleRequest->server['request_uri']);
        } catch (\JSwoole\Route\RouteException $e) {
            return $swooleResponse->end(json_encode(['code'=>404, 'msg'=>'请求不存在']));
        }

        \JSwoole\JSwoole::app()->loadComponents();
    
        $controller='\\'.\JSwoole\JSwoole::getWorkerContext()->getConfig('controller_namespace').$controller;
        $request=\JSwoole\Request::createFromSwoole($swooleRequest);
        $controllerInstance=new $controller($request);
        $response=$controllerInstance->$action();
    
        foreach ($response->getHeaders() as $name=>$values) {
            $swooleResponse->header($name, implode(', ', $values));
        }
        $swooleResponse->status($response->getStatusCode());
        $swooleResponse->end($response->getBody());
        
    } catch (\Exception $e) {
        var_dump($e->getMessage());
    } finally {
        \JSwoole\JSwoole::removeRequestContext();
    }
});
$server->start();
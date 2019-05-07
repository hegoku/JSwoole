<?php
ini_set('memory_limit','-1');

$cli_options=getopt('Dp:h:');
$port=9501;
$host='0.0.0.0';
if (isset($cli_options['p'])) {
    $port=$cli_options['p'];
}
if (isset($cli_options['h'])) {
    $port=$cli_options['h'];
}

\Swoole\Runtime::enableCoroutine();
$server=new swoole_http_server($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

$server->set([
    // 'reactor_num' => 1,
    // 'worker_num' => 1,
    'max_request' => 1000,
    'daemonize' => isset($cli_options['D'])?1:0,
    'dispatch_mode' => 3,
    'reload_async' => true,
    'pid_file' => __DIR__.'/runtime/server.pid',
    'log_file'=>__DIR__.'/runtime/server.log'
]);

$server->on("start", function ($server) use($host, $port, $cli_options){
    if (!isset($cli_options['D'])) {
        echo "Swoole http server is started at http://$host:$port\n";
    }
});

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

    \JSwoole\JSwoole::$base_path=__DIR__;
    $app_config=require_once('config/app.php');
    \JSwoole\JSwoole::initWorderContext($worker_id, $app_config);
});
$server->on('WorkerStop', function(swoole_server $server, int $worker_id){
    
});
$server->on('request', function($swooleRequest, $swooleResponse) use($cli_options){
    if ($swooleRequest->server['request_uri']=='/favicon.ico') {
        return $swooleResponse->end('');
    }
    \JSwoole\JSwoole::addRequestContext();
    try {
        \JSwoole\JSwoole::app()->loadComponents();

        $route=new \JSwoole\Route\Route();
        $route->loadRouter(\JSwoole\JSwoole::getWorkerContext()->getConfig('route'));
        $controller='';
        $action='';
        try {
            list($controller, $action)=$route->parseUri($swooleRequest->server['request_method'], $swooleRequest->server['request_uri']);
        } catch (\JSwoole\Route\RouteException $e) {
            $swooleResponse->status(404);
            return $swooleResponse->end(json_encode(['code'=>404, 'msg'=>'请求不存在']));
        }
    
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
        if (!isset($cli_options['D'])) {
            var_dump($e->getMessage());
        }
        \JSwoole\JSwoole::app()->log->log($e->getMessage(), \JSwoole\Log\Log::LEVEL_ERROR, 'app');
        $swooleResponse->status(500);
        $swooleResponse->end(json_encode(['code'=>500, 'msg'=>'内部服务器错误']));
    } finally {
        \JSwoole\JSwoole::app()->log->flush();
        \JSwoole\JSwoole::removeRequestContext();
    }
});
$server->start();
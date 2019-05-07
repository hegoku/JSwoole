<?php
// ini_set('memory_limit','-1');

$cli_options=getopt('Dp:h:');
$port=9501;
$host='0.0.0.0';
if (isset($cli_options['p'])) {
    $port=$cli_options['p'];
}
if (isset($cli_options['h'])) {
    $port=$cli_options['h'];
}

require_once('vendor/autoload.php');

$env_file = parse_ini_file(__DIR__.'/env');
foreach ($env_file as $k=>$v) {
    putenv($k.'='.$v);
}

$path=[
    'base_path'=>__DIR__,
    'app_config'=>__DIR__.'/config/app.php'
];

$server=new \JSwoole\HttpServer($path, $host, $port, isset($cli_options['D'])?true:false);
$server->run();
<?php
return [
    ['get', '/', 'IndexController@index', 'middlewares'=>['\App\Middlewares\ExampleMiddleware', '\App\Middlewares\Example2Middleware']]
];
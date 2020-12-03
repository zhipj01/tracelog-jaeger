<?php


return [

    'enabled' => env('JAEGER_ENABLED', false),

    'service_name' => env('JAEGER_SERVICE_NAME', env('APP_NAME', 'Laravel')),

    'agent' => [
        'host' => env('OPENTRACING_AGENT','0.0.0.0:6831'),
    ],

    'watchers' => [
        Tracelog\jaeger\watchers\RequestWatcher::class,
        Tracelog\jaeger\watchers\FrameworkWatcher::class
    ],

];

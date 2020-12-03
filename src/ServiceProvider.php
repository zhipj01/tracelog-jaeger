<?php

namespace Tracelog\jaeger;

use Jaeger\Config;
use const Jaeger\Constants\PROPAGATOR_JAEGER;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/config/jaeger.php', 'jaeger');

        $this->app->singleton(TraceJaeger::class, static function ($app) {
            $config = Config::getInstance();

            $config->gen128bit();
            $config::$propagator = PROPAGATOR_JAEGER;

            $client = $config->initTracer(
                config('jaeger.service_name'),
                config('jaeger.agent.host')
            );

            return new TraceJaeger($app, $client);
        });

        if (config('jaeger.enabled')) {
            foreach (config('jaeger.watchers', []) as $watcher) {
                resolve($watcher)->register();
            }
        }
    }

    public function boot()
    {
        $this->publishes([__DIR__.'/config/jaeger.php' => config_path('jaeger.php')],'config');
    }

}

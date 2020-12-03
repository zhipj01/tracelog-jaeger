<?php


namespace Tracelog\jaeger\facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class TraceJaeger
 * @package Tracelog\jaeger\facades
 * @method static client
 * @method static getRootSpan
 * @method static setRootSpan
 * @method static inject(array $target = [])
 * @method static finish
 * @method static flush
 */
class TraceJaeger extends Facade
{

    public static function getFacadeAccessor()
    {
        return \Tracelog\jaeger\TraceJaeger::class;
    }

}

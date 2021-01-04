<?php


namespace Tracelog\jaeger\watchers;


use Jaeger\Span;
use Tracelog\jaeger\TraceJaeger as Jaeger;

class FrameworkWatcher
{
    protected $jaeger;

    /** @var Span */
    protected $initialisationSpan;

    /** @var Span */
    protected $frameworkBootingSpan;

    /** @var Span */
    protected $frameworkRunningSpan;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }

    public function register(): void
    {
        app()->terminating(function () {
            $rootSpan = $this->jaeger->getRootSpan();

            $rootSpan->startTime = (int)(LARAVEL_START * 1000000);

            $rootSpan->finish();

            if (config('jaeger.enabled')) {
                $this->jaeger->client()->flush();
            }
        });
    }

}

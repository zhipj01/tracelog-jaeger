<?php

namespace Tracelog\jaeger;

use Illuminate\Contracts\Foundation\Application;
use OpenTracing\Tracer;
use Jaeger\Span;
use const OpenTracing\Formats\TEXT_MAP;
use Illuminate\Support\Arr;

class TraceJaeger
{
    protected $app;
    protected $tracer;

    /**
     * @var Span
     */
    protected $rootSpan;

    public function __construct(Application $application, Tracer $tracer)
    {
        if (!$this->shouldTrace()){
            return false;
        }

        $this->app = $application;
        $this->tracer = $tracer;

    }

    public function client()
    {
        return $this->tracer;
    }

    public function getRootSpan()
    {
        if ($this->rootSpan) {
            return $this->rootSpan;
        }

        $target = [];

        foreach (request()->headers->all() as $key => $value) {
            $target[$key] = Arr::first($value);
        }

        $spanContext = $this->tracer->extract(TEXT_MAP, $target);

        $spanContext
            ? $this->rootSpan = $this->tracer->startSpan('root', ['child_of' => $spanContext])
            : $this->rootSpan = $this->tracer->startSpan('root');


        $this->rootSpan->setTag('type', $this->app->runningInConsole() ? 'console' : 'http');
        $this->rootSpan->setTag('laravel.version', $this->app->version());

        return $this->rootSpan;
    }

    public function setRootSpan(Span $rootSpan): void
    {
        $this->rootSpan = $rootSpan;
    }

    public function inject(array $target)
    {
        $this->tracer->inject($this->getRootSpan()->getContext(), TEXT_MAP, $target);
        return $target;
    }

    public function shouldTrace()
    {
        return config('jaeger.enabled');
    }

    public function finish()
    {
        $this->rootSpan->finish();
    }

    public function flush()
    {
        $this->tracer->flush();
    }

}

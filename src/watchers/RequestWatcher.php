<?php


namespace Tracelog\jaeger\watchers;


use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Tracelog\jaeger\TraceJaeger;

class RequestWatcher
{

    protected $jaeger;

    public function __construct(TraceJaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }

    public function register()
    {

        Event::listen(RequestHandled::class,function (RequestHandled $event){
            $rootSpan = $this->jaeger->getRootSpan();

            $rootSpan->overwriteOperationName($event->request->method().':'.optional($event->request->route())->uri() ?? $event->request->getPathInfo());
            $rootSpan->setTag('http.host', $event->request->getHost());
            $rootSpan->setTag('http.route', str_replace($event->request->root(), '', $event->request->fullUrl()) ?: '/');
            $rootSpan->setTag('http.method', $event->request->method());
            $rootSpan->setTag('http.status_code', (string)$event->response->getStatusCode());
            $rootSpan->setTag('http.error', $event->response->isSuccessful() ? 'false' : 'true');
            $rootSpan->setTag('controller_action', optional($event->request->route())->getActionName());

            $data = $event->request->toArray();
            isset($data['password']) && $data['password'] = md5($data['password']);
            isset($data['pwd']) && $data['pwd'] = md5($data['pwd']);

            $rootSpan->log(['requestData' => [
                'data' => $data,
                'headers' => $event->request->header()
            ]]);

            $rootSpan->log(['responseData' => $event->response->getContent()]);

            $this->jaeger->setRootSpan($rootSpan);
        });
    }

}

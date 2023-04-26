<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    public function handle($request, Closure $next)
    {
        $request->start = microtime(true);
        $request = $this->identifyRequest($request);

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $request->end = microtime(true);

        $this->log($request,$response);
    }

    protected function log($request,$response)
    {
        $duration = floor(($request->end - $request->start) * 1000);
        $url = $request->fullUrl();
        $name = "";
        $request_id = $request->header('request_id');
        $end = date('Y-m-d H:i:s',$request->end);
        $start = date('Y-m-d H:i:s',$request->start);
        $method = $request->getMethod();
        $route = optional($request->route())->getName() ?? optional($request->route())->uri();
        $path  = $request->path();
        $status = $response->getStatusCode();
        $ip = $request->getClientIp();
        $request_body = json_decode($request->getContent(), true);
        $request_header = $request->headers->all();
        $response_body = json_decode($response->getContent(), true);
        $response_header = $response->headers->all();
        $memory = memory_get_peak_usage(true);

        $log = [
            "request_id" => $request_id,
            "name" => $name,
            "duration" => $duration,
            "url" => $url,
            "end" => $end,
            "start" => $start,
            "method" => $method,
            "route" => $route,
            "path" => $path,
            "status" => $status,
            "ip" => $ip,
            "request_body" => $request_body,
            "request_header" => $request_header ,
            "response_body" => $response_body ,
            "response_header" => $response_header ,
            "memory" => round($memory / 1024 / 1024, 1),
        ];

        Log::info($log);
    }

    protected function identifyRequest(Request $request)
    {
        $requestId = $request->getUniqueId();
        $request->headers->add(['request_id' => $requestId]);
        return $request;
    }
}

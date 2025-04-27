<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MeasureResponseTime
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $time = microtime(true) - $start;
        $timeMs = round($time * 1000);

        $response->headers->set('X-Response-Time', $timeMs . 'ms');

        if ($timeMs > 200) {
            Log::warning("Slow API response: {$request->method()} {$request->path()} - {$timeMs}ms");
        }

        return $response;
    }
}

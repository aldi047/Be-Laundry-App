<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class ResponseLoggingMiddleware
{
    /**
     * Log every outgoing response with useful context.
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Capture response body and represent it as array if JSON
        $rawBody = '';
        try {
            if (method_exists($response, 'getContent')) {
                $rawBody = (string) $response->getContent();
            } else {
                $rawBody = '[no content accessor]';
            }
        } catch (\Throwable $e) {
            $rawBody = '[unavailable: ' . $e->getMessage() . ']';
        }

        $decoded = null;
        if (is_string($rawBody)) {
            $decoded = json_decode($rawBody, true);
        }

        $maxLen = 1000;
        $bodyArray = is_array($decoded)
            ? $decoded
            : ['raw' => (is_string($rawBody) && strlen($rawBody) > $maxLen)
                ? substr($rawBody, 0, $maxLen) . '... [truncated]'
                : $rawBody];

        // Log only path and response body as array
        $context = [
            'path' => $request->path(),
            'response_body' => $bodyArray,
        ];

        Log::info($context);

        return $response;
    }
}
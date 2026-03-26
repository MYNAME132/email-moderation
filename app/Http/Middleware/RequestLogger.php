<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            Log::info('Incoming Request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'query' => $request->query(),
                'payload' => $this->truncatePayload($request->all()),
            ]);

            $response = $next($request);
            Log::info('Outgoing Response', [
                'status' => $response->getStatusCode(),
                'content' => $this->truncateContent($response->getContent()),
            ]);

            return $response;
        } catch (Throwable $e) {
            Log::error('Request handling failed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'payload' => $this->truncatePayload($request->all()),
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function truncatePayload(array $payload, int $maxLength = 1000): array
    {
        return array_map(function ($value) use ($maxLength) {
            if (is_string($value) && strlen($value) > $maxLength) {
                return substr($value, 0, $maxLength) . '...';
            }
            return $value;
        }, $payload);
    }

    private function truncateContent(string $content, int $maxLength = 1000): string
    {
        return strlen($content) > $maxLength ? substr($content, 0, $maxLength) . '...' : $content;
    }
}

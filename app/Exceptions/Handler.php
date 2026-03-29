<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void {}
    public function render($request, Throwable $e): JsonResponse
    {
        Log::error('Unhandled Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'payload' => $request->all()
        ]);

        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }

        // Model not found
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'error' => 'Resource not found'
            ], 404);
        }

        // HTTP exceptions (404, 403, etc.)
        if ($e instanceof HttpExceptionInterface) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage() ?: 'HTTP error'
            ], $e->getStatusCode());
        }

        // Default fallback
        return response()->json([
            'success' => false,
            'error' => 'Internal server error'
        ], 500);
    }
}

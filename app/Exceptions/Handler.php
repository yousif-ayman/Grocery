<?php

namespace App\Exceptions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

  public function render($request, Throwable $e)
{
    if ($request->is('api/*')) {
        return $this->renderApiException($request, $e);
    }

    return parent::render($request, $e);
}
private function renderApiException(
    Request $request,
    Throwable $exception
): JsonResponse {
    if ($exception instanceof AuthenticationException) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated.',
            'data' => null,
        ], 401);
    }

    if ($exception instanceof ValidationException) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'data' => $exception->errors(),
        ], 422);
    }

    $statusCode = $this->getStatusCode($exception);

    return response()->json([
        'status' => 'error',
        'message' => $this->getExceptionMessage($exception),
        'data' => null,
    ], $statusCode);
}
    

    private function getStatusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();

            if ($statusCode >= 400 && $statusCode <= 599) {
                return $statusCode;
            }
        }

        return 500;
    }

    private function getExceptionMessage(Throwable $exception): string
    {
        if (app()->environment('production')) {
            return 'Something went wrong. Please try again later.';
        }

        return $exception->getMessage()
            ?: 'Something went wrong.';
    }
}
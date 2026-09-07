<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'status' => $code >= 400 ? 'error' : 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(
        string $message,
        mixed $data = null,
        int $code = 400
    ): JsonResponse {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
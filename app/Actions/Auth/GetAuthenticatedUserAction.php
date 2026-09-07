<?php

namespace App\Actions\Auth;

use App\Http\Resources\V1\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAuthenticatedUserAction
{
    use ApiResponse;

    public function __invoke(Request $request): JsonResponse
    {
        return $this->successResponse(
            'User retrieved successfully',
            [
                'user' => new UserResource($request->user()),
            ]
        );
    }
}
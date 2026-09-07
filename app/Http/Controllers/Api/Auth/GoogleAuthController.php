<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Services\GoogleAuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class GoogleAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected GoogleAuthService $googleAuthService
    ) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $user = $this->googleAuthService->login(
                $request->input('id_token')
            );

            $token = $this->googleAuthService->createToken(
                $user,
                $request->input('device_name', 'google_auth')
            );

            return $this->successResponse(
                'Login successful',
                [
                    'user' => new UserResource($user),
                    'token' => $token,
                ]
            );
        } catch (InvalidArgumentException $e) {
           return $this->errorResponse(
    $e->getMessage(),
    null,
    401
);
        } catch (RuntimeException $e) {
            if (
                $e->getMessage() ===
                'Your account has been deactivated.'
            ) {
               return $this->errorResponse(
    $e->getMessage(),
    null,
    403
);
            }
            return $this->errorResponse(
    $e->getMessage(),
    null,
    503
);
            ;
        }
    }
}


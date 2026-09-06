<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\Auth\RegisterUserAction;

use App\Actions\Auth\ChangePasswordAction;
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

   public function register(
    RegisterRequest $request,
    RegisterUserAction $action
): JsonResponse {
    $result = $action->execute(
        $request->validated()
    );

    return $this->successResponse(
        'Registration successful',
        [
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ],
        201
    );
}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated()
        );

        return $this->successResponse(
            'Login successful',
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(
            'Logout successful'
        );
    }

    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {
        $this->authService->forgotPassword(
            $request->validated('identifier')
        );

        return $this->successResponse(
            'OTP sent successfully. Please check your email or phone.'
        );
    }

    public function verifyOtp(
        VerifyOtpRequest $request
    ): JsonResponse {
        $this->authService->verifyOtp(
            $request->validated()
        );

        return $this->successResponse(
            'OTP verified successfully.'
        );
    }

    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $this->authService->resetPassword(
            $request->validated()
        );

        return $this->successResponse(
            'Password reset successfully.'
        );
    }

    

    public function deleteAccount(
        DeleteAccountRequest $request
    ): JsonResponse {
        $this->authService->deleteAccount(
            $request->user()
        );

        return $this->successResponse(
            'Account deleted successfully.'
        );
    }
public function changePassword(
    ChangePasswordRequest $request,
    ChangePasswordAction $action
): JsonResponse {
    $action->execute(
        $request->user(),
        $request->validated()
    );

    return $this->successResponse(
        'Password changed successfully.'
    );
}
   
}
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const PASSWORD_RESET_TYPE = 'password_reset';

    public function __construct(
        protected OtpService $otpService,
        protected NotificationService $notificationService
    ) {}

  


    public function login(array $data): array
    {
        $user = User::findByIdentifier($data['login']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => [
                    'Unable to sign in. Please check your credentials.',
                ],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => [
                    'Your account has been deactivated.',
                ],
            ]);
        }

        return [
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    public function forgotPassword(string $identifier): void
    {
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => [
                    'User not found.',
                ],
            ]);
        }

        $otp = $this->otpService->generate(
            $identifier,
            self::PASSWORD_RESET_TYPE
        );

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $this->notificationService->sendOtpEmail(
                $identifier,
                $otp,
                self::PASSWORD_RESET_TYPE
            );

            return;
        }

        $this->notificationService->sendOtpSms(
            $identifier,
            $otp,
            self::PASSWORD_RESET_TYPE
        );
    }

    public function verifyOtp(array $data): void
    {
        $isValid = $this->otpService->isValid(
            $data['identifier'],
            $data['otp'],
            self::PASSWORD_RESET_TYPE
        );

        if (! $isValid) {
            throw ValidationException::withMessages([
                'otp' => [
                    'The OTP is invalid or has expired.',
                ],
            ]);
        }
    }

    public function resetPassword(array $data): void
    {
        $isValid = $this->otpService->isValid(
            $data['identifier'],
            $data['otp'],
            self::PASSWORD_RESET_TYPE
        );

        if (! $isValid) {
            throw ValidationException::withMessages([
                'otp' => [
                    'The OTP is invalid or has expired.',
                ],
            ]);
        }

        $user = User::findByIdentifier($data['identifier']);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => [
                    'User not found.',
                ],
            ]);
        }

        $user->update([
            'password' => $data['password'],
        ]);

        $user->tokens()->delete();

        $this->otpService->verify(
            $data['identifier'],
            $data['otp'],
            self::PASSWORD_RESET_TYPE
        );
    }

    

    public function deleteAccount(User $user): void
    {
        $user->tokens()->delete();

        $user->delete();
    }
}
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

    public function register(array $data): array
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'agree_terms' => $data['agree_terms'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        if ($user->email) {
            $this->notificationService->sendWelcomeEmail(
                $user->email,
                $user->username
            );
        }

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

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

    public function changePassword(User $user, array $data): void
    {
        if (! Hash::check(
            $data['current_password'],
            $user->password
        )) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'The current password is incorrect.',
                ],
            ]);
        }

        if (Hash::check(
            $data['password'],
            $user->password
        )) {
            throw ValidationException::withMessages([
                'password' => [
                    'The new password must be different from the current password.',
                ],
            ]);
        }

        $user->update([
            'password' => $data['password'],
        ]);
    }

    public function deleteAccount(User $user): void
    {
        $user->tokens()->delete();

        $user->delete();
    }
}
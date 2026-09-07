<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\NotificationService;

class RegisterUserAction
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function execute(array $data): array
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
}
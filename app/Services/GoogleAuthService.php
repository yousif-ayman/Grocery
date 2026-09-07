<?php

namespace App\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthService
{
    private const ALLOWED_GOOGLE_ISSUERS = [
        'accounts.google.com',
        'https://accounts.google.com',
    ];

    public function login(string $idToken): User
    {
        $allowedClientIds = $this->allowedGoogleClientIds();

        if (empty($allowedClientIds)) {
            throw new \RuntimeException('Google sign-in is not configured.');
        }

        try {
            $client = new GoogleClient();

            $payload = $client->verifyIdToken($idToken);
        } catch (Throwable $e) {
            Log::warning('Google ID token verification failed', [
                'message' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException(
                'Invalid Google token.'
            );
        }

        if (
            ! is_array($payload) ||
            ! $this->isValidGooglePayload(
                $payload,
                $allowedClientIds
            )
        ) {
            throw new \InvalidArgumentException(
                'Invalid Google token.'
            );
        }

        $email = strtolower((string) $payload['email']);

        $user = User::where('email', $email)->first();

        if ($user) {
            if (! $user->is_active) {
                throw new \RuntimeException(
                    'Your account has been deactivated.'
                );
            }

            $user->fill([
                'google_id' => $payload['sub'] ?? $user->google_id,
                'avatar' => $payload['picture'] ?? $user->avatar,
            ]);

            if (! $user->email_verified) {
                $user->email_verified = true;
                $user->email_verified_at = now();
            }

            $user->save();

            return $user;
        }

        return User::create([
            'username' => $this->uniqueUsernameForGoogle(
                $payload,
                $email
            ),
            'email' => $email,
            'google_id' => $payload['sub'] ?? null,
            'avatar' => $payload['picture'] ?? null,
            'password' => Str::random(32),
            'agree_terms' => true,
            'email_verified' => true,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    public function createToken(
        User $user,
        string $deviceName = 'google_auth'
    ): string {
        $deviceName = trim($deviceName);

        return $user
            ->createToken(
                $deviceName !== '' ? $deviceName : 'google_auth'
            )
            ->plainTextToken;
    }

    private function allowedGoogleClientIds(): array
    {
        $clientIds = config(
            'services.google.client_ids',
            []
        );

        if (! is_array($clientIds)) {
            $clientIds = [];
        }

        $legacyClientId = config(
            'services.google.client_id'
        );

        if (
            is_string($legacyClientId) &&
            trim($legacyClientId) !== ''
        ) {
            $clientIds[] = $legacyClientId;
        }

        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn ($id) =>
                            is_string($id)
                                ? trim($id)
                                : '',
                        $clientIds
                    )
                )
            )
        );
    }

    private function isValidGooglePayload(
        array $payload,
        array $allowedClientIds
    ): bool {
        $audience = (string) ($payload['aud'] ?? '');
        $issuer = (string) ($payload['iss'] ?? '');
        $email = (string) ($payload['email'] ?? '');
        $emailVerified = filter_var(
            $payload['email_verified'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
        $subject = (string) ($payload['sub'] ?? '');
        $expiry = (int) ($payload['exp'] ?? 0);

        return $audience !== ''
            && in_array(
                $audience,
                $allowedClientIds,
                true
            )
            && in_array(
                $issuer,
                self::ALLOWED_GOOGLE_ISSUERS,
                true
            )
            && $email !== ''
            && $emailVerified
            && $subject !== ''
            && $expiry > now()->timestamp;
    }

    private function uniqueUsernameForGoogle(
        array $payload,
        string $email
    ): string {
        $fromName = Str::slug(
            (string) ($payload['name'] ?? '')
        );

        $fromEmail = Str::slug(
            Str::before($email, '@')
        );

        $base = 'user';

        if ($fromName !== '') {
            $base = $fromName;
        } elseif ($fromEmail !== '') {
            $base = $fromEmail;
        }

        $base = Str::limit(
            $base,
            User::USERNAME_MAX_LENGTH - 4,
            ''
        );

        if ($base === '') {
            $base = 'user';
        }

        $candidate = Str::limit(
            $base,
            User::USERNAME_MAX_LENGTH,
            ''
        );

        if (! preg_match('/\p{L}/u', $candidate)) {
            $candidate = Str::limit(
                'user_' . $candidate,
                User::USERNAME_MAX_LENGTH,
                ''
            );
        }

        $n = 0;

        while (
         User::where('username', $candidate)->exists()
        ) {
            $n++;

            $suffix = (string) $n;

            $candidate =
                Str::limit(
                    $base,
                    User::USERNAME_MAX_LENGTH -
                    strlen($suffix),
                    ''
                ) . $suffix;
        }

        return Str::limit(
            $candidate,
            User::USERNAME_MAX_LENGTH,
            ''
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\GoogleProfileDTO;
use App\Domains\Auth\Exceptions\AuthException;
use Illuminate\Support\Facades\Http;

/** Infrastructure adapter: verifies a Google ID token and returns the profile. */
final class GoogleIdTokenVerifier
{
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    public function verify(string $credential): GoogleProfileDTO
    {
        $response = Http::timeout(10)->get(self::TOKENINFO_URL, ['id_token' => $credential]);

        if ($response->failed()) {
            throw AuthException::invalidGoogleToken();
        }

        $data = $response->json();

        // 'aud' must match our client id, otherwise the token was issued for another app.
        if (($data['aud'] ?? null) !== config('services.google.client_id')) {
            throw AuthException::invalidGoogleToken();
        }

        $googleId = $data['sub'] ?? null;
        $email = $data['email'] ?? null;
        $verified = filter_var($data['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $googleId || ! $email || ! $verified) {
            throw AuthException::incompleteGoogleProfile();
        }

        return new GoogleProfileDTO(
            googleId: (string) $googleId,
            email: (string) $email,
            name: (string) ($data['name'] ?? $email),
        );
    }
}

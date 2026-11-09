<?php

declare(strict_types=1);

namespace App\Domains\Auth\DTOs;

use App\Domains\Auth\Models\User;

/** Result of any action that opens a session (login, google, refresh). */
final readonly class AuthSessionDTO
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public User $user,
    ) {}
}

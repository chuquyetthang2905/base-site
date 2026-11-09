<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\AuthSessionDTO;
use App\Domains\Auth\Models\RefreshToken;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Opens a session: short-lived JWT + rotating refresh token.
 * A new family starts on login; refresh passes the existing $familyId to keep the chain.
 */
final readonly class IssueAuthSessionAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function execute(User $user, ?string $familyId = null): AuthSessionDTO
    {
        $rawToken = bin2hex(random_bytes(64));

        RefreshToken::query()->create([
            'user_id' => $user->id,
            'token' => RefreshToken::hash($rawToken),
            'family_id' => $familyId ?? Str::uuid()->toString(),
            'expires_at' => now()->addDays((int) config('app.refresh_token_ttl_days', 30)),
        ]);

        return new AuthSessionDTO(
            accessToken: JWTAuth::fromUser($user),
            refreshToken: $rawToken,
            user: $this->users->findWithRoles($user->id),
        );
    }
}

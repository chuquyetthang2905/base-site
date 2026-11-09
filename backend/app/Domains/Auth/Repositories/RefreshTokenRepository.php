<?php

declare(strict_types=1);

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\Models\RefreshToken;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function findByHash(string $hashedToken): ?RefreshToken
    {
        return RefreshToken::query()->where('token', $hashedToken)->first();
    }

    public function revokeFamily(string $familyId): int
    {
        return RefreshToken::query()
            ->where('family_id', $familyId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function revokeByHash(string $hashedToken): int
    {
        return RefreshToken::query()
            ->where('token', $hashedToken)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}

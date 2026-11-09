<?php

declare(strict_types=1);

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\Models\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function findByHash(string $hashedToken): ?RefreshToken;

    /** Revoke every still-active token of a rotation family (theft detection). */
    public function revokeFamily(string $familyId): int;

    /** Revoke a single active token by its hash (logout). */
    public function revokeByHash(string $hashedToken): int;
}

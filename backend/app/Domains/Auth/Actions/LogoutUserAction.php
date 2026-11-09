<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\LogoutDTO;
use App\Domains\Auth\Models\RefreshToken;
use App\Domains\Auth\Repositories\RefreshTokenRepositoryInterface;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Throwable;

final readonly class LogoutUserAction
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokens,
    ) {}

    public function execute(LogoutDTO $dto): void
    {
        // Blacklist the access token; an already-expired token must not block logout.
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (Throwable) {
            // ignored on purpose
        }

        if ($dto->rawRefreshToken !== null && $dto->rawRefreshToken !== '') {
            $this->refreshTokens->revokeByHash(RefreshToken::hash($dto->rawRefreshToken));
        }
    }
}

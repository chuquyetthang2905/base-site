<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\AuthSessionDTO;
use App\Domains\Auth\DTOs\RefreshTokenDTO;
use App\Domains\Auth\Events\RefreshTokenReuseDetected;
use App\Domains\Auth\Exceptions\AuthException;
use App\Domains\Auth\Models\RefreshToken;
use App\Domains\Auth\Repositories\RefreshTokenRepositoryInterface;

/**
 * Refresh-token rotation with theft detection:
 * a revoked token presented again revokes its whole family and forces re-login.
 * Deliberately NOT wrapped in a DB transaction, otherwise the family revocation
 * would be rolled back when the exception is thrown.
 */
final readonly class RefreshTokenAction
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokens,
        private IssueAuthSessionAction $issueSession,
    ) {}

    public function execute(RefreshTokenDTO $dto): AuthSessionDTO
    {
        $token = $this->refreshTokens->findByHash(RefreshToken::hash($dto->rawToken))
            ?? throw AuthException::invalidRefreshToken();

        if ($token->isRevoked()) {
            $this->refreshTokens->revokeFamily($token->family_id);

            RefreshTokenReuseDetected::dispatch($token->user_id, $token->family_id);

            throw AuthException::refreshTokenReused();
        }

        if (! $token->isValid()) {
            throw AuthException::expiredRefreshToken();
        }

        $user = $token->user;

        if (! $user->isActive()) {
            throw AuthException::accountDeactivated();
        }

        $token->update(['revoked_at' => now()]);

        return $this->issueSession->execute($user, familyId: $token->family_id);
    }
}

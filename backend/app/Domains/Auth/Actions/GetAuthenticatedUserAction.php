<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\Exceptions\AuthException;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

final readonly class GetAuthenticatedUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function execute(int $userId): User
    {
        return $this->users->findWithRoles($userId)
            ?? throw AuthException::invalidCredentials();
    }
}

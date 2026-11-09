<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\AuthSessionDTO;
use App\Domains\Auth\DTOs\LoginDTO;
use App\Domains\Auth\Exceptions\AuthException;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final readonly class LoginUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private IssueAuthSessionAction $issueSession,
    ) {}

    public function execute(LoginDTO $dto): AuthSessionDTO
    {
        $user = $this->users->findByEmail($dto->email);

        if ($user === null || ! Hash::check($dto->password, (string) $user->password)) {
            throw AuthException::invalidCredentials();
        }

        if (! $user->isActive()) {
            throw AuthException::accountDeactivated();
        }

        return $this->issueSession->execute($user);
    }
}

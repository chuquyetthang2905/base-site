<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\AuthSessionDTO;
use App\Domains\Auth\DTOs\GoogleLoginDTO;
use App\Domains\Auth\Events\UserRegistered;
use App\Domains\Auth\Exceptions\AuthException;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Auth\Services\GoogleIdTokenVerifier;
use Illuminate\Support\Facades\DB;

final readonly class LoginWithGoogleAction
{
    public function __construct(
        private GoogleIdTokenVerifier $verifier,
        private UserRepositoryInterface $users,
        private AssignDefaultRoleAction $assignDefaultRole,
        private IssueAuthSessionAction $issueSession,
    ) {}

    public function execute(GoogleLoginDTO $dto): AuthSessionDTO
    {
        $profile = $this->verifier->verify($dto->credential);

        $user = $this->users->findByGoogleId($profile->googleId)
            ?? $this->linkExistingAccount($profile->email, $profile->googleId)
            ?? $this->createAccount($profile->name, $profile->email, $profile->googleId);

        if (! $user->isActive()) {
            throw AuthException::accountDeactivated();
        }

        return $this->issueSession->execute($user);
    }

    /** Same verified email already registered locally: attach the Google identity to it. */
    private function linkExistingAccount(string $email, string $googleId): ?User
    {
        $user = $this->users->findByEmail($email);

        $user?->update(['google_id' => $googleId]);

        return $user;
    }

    private function createAccount(string $name, string $email, string $googleId): User
    {
        $user = DB::transaction(function () use ($name, $email, $googleId): User {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'is_active' => true,
                'password' => null,
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            // Fixes the legacy bug: Google users used to get no role.
            $this->assignDefaultRole->execute($user);

            return $user;
        });

        UserRegistered::dispatch($user);

        return $user;
    }
}

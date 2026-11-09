<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\DTOs\RegisterDTO;
use App\Domains\Auth\Events\UserRegistered;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

/** No token is issued on register: the user must log in explicitly. */
final readonly class RegisterUserAction
{
    public function __construct(
        private AssignDefaultRoleAction $assignDefaultRole,
        private UserRepositoryInterface $users,
    ) {}

    public function execute(RegisterDTO $dto): User
    {
        $user = DB::transaction(function () use ($dto): User {
            $user = User::query()->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => $dto->password, // hashed by the model cast
            ]);

            $this->assignDefaultRole->execute($user);

            return $user;
        });

        UserRegistered::dispatch($user);

        return $this->users->findWithRoles($user->id);
    }
}

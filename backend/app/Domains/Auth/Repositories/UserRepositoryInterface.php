<?php

declare(strict_types=1);

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByGoogleId(string $googleId): ?User;

    /** Load a user with roles.permissions eager-loaded (avoids N+1 on role/permission checks). */
    public function findWithRoles(int $id): ?User;
}

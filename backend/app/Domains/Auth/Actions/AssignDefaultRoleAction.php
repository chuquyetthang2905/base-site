<?php

declare(strict_types=1);

namespace App\Domains\Auth\Actions;

use App\Domains\Auth\Enums\RoleName;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;

final class AssignDefaultRoleAction
{
    public function execute(User $user): void
    {
        $role = Role::query()->firstOrCreate(
            ['name' => RoleName::User->value],
            ['display_name' => RoleName::User->displayName()],
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}

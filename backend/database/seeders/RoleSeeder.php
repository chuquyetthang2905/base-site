<?php

namespace Database\Seeders;

use App\Domains\Auth\Enums\RoleName;
use App\Domains\Auth\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleName::cases() as $role) {
            Role::query()->firstOrCreate(
                ['name' => $role->value],
                ['display_name' => $role->displayName()],
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Auth\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case User = 'user';

    public function displayName(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::User => 'User',
        };
    }
}

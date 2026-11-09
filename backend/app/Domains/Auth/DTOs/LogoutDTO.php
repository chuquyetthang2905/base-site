<?php

declare(strict_types=1);

namespace App\Domains\Auth\DTOs;

final readonly class LogoutDTO
{
    public function __construct(
        public ?string $rawRefreshToken,
    ) {}
}

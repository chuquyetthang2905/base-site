<?php

declare(strict_types=1);

namespace App\Domains\Auth\DTOs;

final readonly class RefreshTokenDTO
{
    public function __construct(
        public string $rawToken,
    ) {}
}

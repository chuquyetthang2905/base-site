<?php

declare(strict_types=1);

namespace App\Domains\Auth\DTOs;

final readonly class GoogleProfileDTO
{
    public function __construct(
        public string $googleId,
        public string $email,
        public string $name,
    ) {}
}

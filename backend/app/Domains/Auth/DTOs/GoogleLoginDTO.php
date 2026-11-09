<?php

declare(strict_types=1);

namespace App\Domains\Auth\DTOs;

final readonly class GoogleLoginDTO
{
    public function __construct(
        public string $credential,
    ) {}

    /** @param array{credential: string} $validated */
    public static function fromValidated(array $validated): self
    {
        return new self(credential: $validated['credential']);
    }
}

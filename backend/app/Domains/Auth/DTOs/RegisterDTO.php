<?php

declare(strict_types=1);

namespace App\Domains\Auth\DTOs;

/** Plain-text password: hashing is done by the 'hashed' cast on User. */
final readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /** @param array{name: string, email: string, password: string} $validated */
    public static function fromValidated(array $validated): self
    {
        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}

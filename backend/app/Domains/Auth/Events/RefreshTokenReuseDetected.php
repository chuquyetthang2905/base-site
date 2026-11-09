<?php

declare(strict_types=1);

namespace App\Domains\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class RefreshTokenReuseDetected
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public string $familyId,
    ) {}
}

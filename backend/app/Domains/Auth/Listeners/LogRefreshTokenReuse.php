<?php

declare(strict_types=1);

namespace App\Domains\Auth\Listeners;

use App\Domains\Auth\Events\RefreshTokenReuseDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class LogRefreshTokenReuse implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(RefreshTokenReuseDetected $event): void
    {
        Log::warning('Refresh token reuse detected — full family revoked', [
            'user_id' => $event->userId,
            'family_id' => $event->familyId,
        ]);
    }
}

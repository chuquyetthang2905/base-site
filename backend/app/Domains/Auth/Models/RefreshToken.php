<?php

declare(strict_types=1);

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'token', 'family_id', 'expires_at', 'revoked_at'])]
class RefreshToken extends Model
{
    // The table only has created_at (see migration).
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** Only the SHA-256 hash is stored; the raw token lives in the client's HttpOnly cookie. */
    public static function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isRevoked() && $this->expires_at->isFuture();
    }
}

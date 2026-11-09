<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Stored as SHA-256 hash (64-char hex) of the actual token.
            // The raw token only ever exists in the HttpOnly cookie on the client.
            // If this DB is compromised, the hashed values are useless to an attacker.
            $table->string('token', 64)->unique();

            // Groups a full rotation chain (token → rotated token → rotated token...).
            // If a token from a previous rotation is replayed, we detect it
            // and revoke the entire family — indicating the token was stolen.
            $table->uuid('family_id')->index();

            // When this token naturally expires (default: 30 days from creation).
            $table->timestamp('expires_at');

            // Set when the token is explicitly invalidated before its expiry.
            // Causes: logout, rotation (old token revoked), theft detection.
            $table->timestamp('revoked_at')->nullable();

            // Only created_at — refresh tokens are immutable, never updated.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};

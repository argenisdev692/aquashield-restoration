<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create socialite_providers table.
 *
 * Stores OAuth connections between users and third-party providers.
 * A user can have multiple providers (Google, GitHub, etc.).
 * Each provider connection stores the provider-specific ID, tokens,
 * avatar URL, and refresh capabilities.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('socialite_providers', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('provider');             // google, github, facebook, etc.
            $table->string('provider_id');           // unique ID from OAuth provider
            $table->string('provider_email')->nullable();
            $table->string('nickname')->nullable();
            $table->string('avatar')->nullable();     // profile photo URL from provider
            $table->text('token');                    // access token
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ──
            $table->unique(['provider', 'provider_id']); // one provider account = one link
            $table->index(['user_id', 'provider']);       // fast lookup per user per provider
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socialite_providers');
    }
};

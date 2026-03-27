<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('title');
            $table->string('niche');
            $table->enum('platform', ['tiktok', 'instagram', 'facebook']);
            $table->text('caption')->nullable();
            $table->text('hashtags')->nullable();
            $table->text('call_to_action')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('status', ['draft', 'generated', 'published'])->default('draft');
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_campaigns');
    }
};

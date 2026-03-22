<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('call_histories', static function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('call_id')->unique()->comment('Retell AI call ID');
            $table->string('agent_id')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('from_number')->nullable();
            $table->string('to_number')->nullable();
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->enum('call_status', ['registered', 'ongoing', 'ended', 'error'])->default('registered');
            $table->timestamp('start_timestamp')->nullable();
            $table->timestamp('end_timestamp')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->longText('transcript')->nullable();
            $table->text('recording_url')->nullable();
            $table->json('call_analysis')->nullable();
            $table->string('disconnection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('call_type', ['lead', 'appointment', 'support', 'other'])->default('other');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['call_status', 'start_timestamp']);
            $table->index(['direction', 'call_type']);
            $table->index(['from_number']);
            $table->index(['to_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_histories');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scope_sheets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('claim_id')->constrained('claims')->onUpdate('cascade')->onDelete('cascade');
            $table->string('scope_sheet_description')->nullable();
            $table->foreignId('generated_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['claim_id']);
            $table->index(['generated_by']);
            $table->index(['deleted_at']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_sheets');
    }
};

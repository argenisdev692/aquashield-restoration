<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scope_sheet_exports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('scope_sheet_id')->constrained('scope_sheets')->onUpdate('cascade')->onDelete('cascade');
            $table->string('full_pdf_path');
            $table->foreignId('generated_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();

            $table->index(['scope_sheet_id']);
            $table->index(['generated_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_sheet_exports');
    }
};

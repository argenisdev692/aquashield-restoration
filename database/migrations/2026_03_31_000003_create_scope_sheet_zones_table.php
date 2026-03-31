<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scope_sheet_zones', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('scope_sheet_id')->constrained('scope_sheets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('zone_id')->constrained('zones')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('zone_order');
            $table->longText('zone_notes')->nullable();
            $table->timestamps();

            $table->index(['scope_sheet_id', 'zone_order']);
            $table->index(['zone_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_sheet_zones');
    }
};

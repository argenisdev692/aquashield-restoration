<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scope_sheet_zone_photos', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('scope_sheet_zone_id')->constrained('scope_sheet_zones')->onUpdate('cascade')->onDelete('cascade');
            $table->string('photo_path');
            $table->integer('photo_order');
            $table->timestamps();

            $table->index(['scope_sheet_zone_id', 'photo_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scope_sheet_zone_photos');
    }
};

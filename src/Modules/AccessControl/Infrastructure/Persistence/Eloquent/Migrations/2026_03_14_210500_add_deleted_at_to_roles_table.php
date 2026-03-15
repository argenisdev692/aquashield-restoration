<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', static function (Blueprint $table): void {
            if (! Schema::hasColumn('roles', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasColumn('roles', 'deleted_at')) {
            return;
        }

        Schema::table('roles', static function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};

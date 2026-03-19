<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('roles', 'deleted_at')) {
            return;
        }

        Schema::table('roles', static function (Blueprint $table): void {
            $table->softDeletes();
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('roles', 'deleted_at')) {
            return;
        }

        Schema::table('roles', static function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropSoftDeletes();
        });
    }
};

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
        Schema::create('document_template_adjusters', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->text('template_description_adjuster')->nullable();
        $table->string('template_type_adjuster');
        $table->string('template_path_adjuster');
        $table->foreignId('public_adjuster_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
        $table->foreignId('uploaded_by')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_template_adjusters');
    }
};

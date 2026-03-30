<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_agreement_alliances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('claim_id')->constrained('claims')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('alliance_company_id')->constrained('alliance_companies')->onUpdate('cascade')->onDelete('cascade');
            $table->string('full_pdf_path');
            
           

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_agreement_alliances');
    }
};

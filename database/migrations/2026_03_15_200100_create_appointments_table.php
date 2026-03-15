<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('country')->nullable();
            $table->boolean('insurance_property')->default(false);
            $table->text('message')->nullable();
            $table->boolean('sms_consent')->default(false);
            $table->dateTime('registration_date')->nullable();
            $table->date('inspection_date')->nullable();
            $table->time('inspection_time')->nullable();
            $table->text('notes')->nullable();
            $table->string('owner')->nullable();
            $table->text('damage_detail')->nullable();
            $table->boolean('intent_to_claim')->default(false);
            $table->string('lead_source')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('additional_note')->nullable();
            $table->string('inspection_status')->default('Pending');
            $table->string('status_lead')->default('New');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

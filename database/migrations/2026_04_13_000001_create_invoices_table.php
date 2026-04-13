<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claim_id')->nullable()->constrained('claims')->nullOnDelete();

            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');

            $table->string('bill_to_name');
            $table->text('bill_to_address')->nullable();
            $table->string('bill_to_phone', 20)->nullable();
            $table->string('bill_to_email', 100)->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);

            $table->string('claim_number')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('insurance_company')->nullable();
            $table->date('date_of_loss')->nullable();
            $table->dateTime('date_received')->nullable();
            $table->dateTime('date_inspected')->nullable();
            $table->dateTime('date_entered')->nullable();

            $table->string('price_list_code')->nullable();
            $table->string('type_of_loss')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled', 'print_pdf'])->default('draft');
            $table->string('pdf_url')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_date');
            $table->index('status');
            $table->index('claim_id');
            $table->index('claim_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

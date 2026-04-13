<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceStatus;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;
use Src\Modules\Invoices\Infrastructure\Persistence\Mappers\InvoiceMapper;

uses(RefreshDatabase::class);

it('maps an Eloquent model to a domain Invoice entity', function (): void {
    $model = new InvoiceEloquentModel([
        'uuid'           => '550e8400-e29b-41d4-a716-446655440000',
        'user_id'        => 1,
        'claim_id'       => null,
        'invoice_number' => 'INV-TEST-001',
        'invoice_date'   => '2026-04-13',
        'bill_to_name'   => 'Test Client',
        'subtotal'       => '1000.00',
        'tax_amount'     => '0.00',
        'balance_due'    => '1000.00',
        'status'         => 'draft',
    ]);

    $mapper  = new InvoiceMapper();
    $invoice = $mapper->toDomain($model);

    expect($invoice->id->toString())->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($invoice->invoiceNumber)->toBe('INV-TEST-001')
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->balanceDue)->toBe(1000.00);
});

it('maps a domain Invoice to persistence array', function (): void {
    $model = new InvoiceEloquentModel([
        'uuid'           => '550e8400-e29b-41d4-a716-446655440000',
        'user_id'        => 1,
        'claim_id'       => null,
        'invoice_number' => 'INV-TEST-001',
        'invoice_date'   => '2026-04-13',
        'bill_to_name'   => 'Test Client',
        'subtotal'       => '500.00',
        'tax_amount'     => '50.00',
        'balance_due'    => '550.00',
        'status'         => 'sent',
    ]);

    $mapper = new InvoiceMapper();
    $domain = $mapper->toDomain($model);
    $data   = $mapper->toPersistence($domain);

    expect($data['invoice_number'])->toBe('INV-TEST-001')
        ->and($data['status'])->toBe('sent')
        ->and($data['balance_due'])->toBe(550.00);
});

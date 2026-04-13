<?php

declare(strict_types=1);

use Src\Modules\Invoices\Domain\Entities\Invoice;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceStatus;

it('creates an invoice with correct defaults', function (): void {
    $invoice = new Invoice(
        id: new InvoiceId('550e8400-e29b-41d4-a716-446655440000'),
        userId: 1,
        claimId: null,
        invoiceNumber: 'INV-0001',
        invoiceDate: '2026-04-13',
        billToName: 'John Doe',
        billToAddress: null,
        billToPhone: null,
        billToEmail: 'john@example.com',
        subtotal: 1000.00,
        taxAmount: 0.00,
        balanceDue: 1000.00,
        claimNumber: null,
        policyNumber: null,
        insuranceCompany: null,
        dateOfLoss: null,
        dateReceived: null,
        dateInspected: null,
        dateEntered: null,
        priceListCode: null,
        typeOfLoss: null,
        notes: null,
        status: InvoiceStatus::Draft,
        pdfUrl: null,
    );

    expect($invoice->invoiceNumber)->toBe('INV-0001')
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->balanceDue)->toBe(1000.00)
        ->and($invoice->items)->toBe([]);
});

it('returns new invoice with updated status via withStatus()', function (): void {
    $invoice = new Invoice(
        id: new InvoiceId('550e8400-e29b-41d4-a716-446655440000'),
        userId: 1,
        claimId: null,
        invoiceNumber: 'INV-0001',
        invoiceDate: '2026-04-13',
        billToName: 'John Doe',
        billToAddress: null,
        billToPhone: null,
        billToEmail: null,
        subtotal: 500.00,
        taxAmount: 50.00,
        balanceDue: 550.00,
        claimNumber: null,
        policyNumber: null,
        insuranceCompany: null,
        dateOfLoss: null,
        dateReceived: null,
        dateInspected: null,
        dateEntered: null,
        priceListCode: null,
        typeOfLoss: null,
        notes: null,
        status: InvoiceStatus::Draft,
        pdfUrl: null,
    );

    $paid = $invoice->withStatus(InvoiceStatus::Paid);

    expect($paid->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->status)->toBe(InvoiceStatus::Draft);
});

it('calculates correct balance_due via withBalanceDue()', function (): void {
    $invoice = new Invoice(
        id: new InvoiceId('550e8400-e29b-41d4-a716-446655440000'),
        userId: 1,
        claimId: null,
        invoiceNumber: 'INV-0002',
        invoiceDate: '2026-04-13',
        billToName: 'Jane Doe',
        billToAddress: null,
        billToPhone: null,
        billToEmail: null,
        subtotal: 0,
        taxAmount: 0,
        balanceDue: 0,
        claimNumber: null,
        policyNumber: null,
        insuranceCompany: null,
        dateOfLoss: null,
        dateReceived: null,
        dateInspected: null,
        dateEntered: null,
        priceListCode: null,
        typeOfLoss: null,
        notes: null,
        status: InvoiceStatus::Draft,
        pdfUrl: null,
    );

    $updated = $invoice->withBalanceDue(800.00, 80.00);

    expect($updated->subtotal)->toBe(800.00)
        ->and($updated->taxAmount)->toBe(80.00)
        ->and($updated->balanceDue)->toBe(880.00);
});

it('throws InvalidArgumentException for invalid UUID', function (): void {
    new InvoiceId('not-a-valid-uuid');
})->throws(\InvalidArgumentException::class);

it('InvoiceStatus enum returns correct label', function (): void {
    expect(InvoiceStatus::Paid->label())->toBe('Paid')
        ->and(InvoiceStatus::PrintPdf->label())->toBe('Print PDF')
        ->and(InvoiceStatus::Cancelled->isActive())->toBeFalse()
        ->and(InvoiceStatus::Draft->isActive())->toBeTrue();
});

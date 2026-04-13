<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Src\Modules\Invoices\Application\Commands\CreateInvoiceHandler;
use Src\Modules\Invoices\Application\DTOs\StoreInvoiceData;
use Src\Modules\Invoices\Domain\Entities\Invoice;
use Src\Modules\Invoices\Domain\Ports\InvoiceRepositoryPort;

it('returns a UUID string when creating an invoice', function (): void {
    $repository = mock(InvoiceRepositoryPort::class, function (MockInterface $mock): void {
        $mock->shouldReceive('save')
            ->once()
            ->withArgs(fn (Invoice $invoice): bool =>
                $invoice->invoiceNumber === 'INV-0001' &&
                $invoice->userId === 1
            )
            ->andReturnUsing(fn (Invoice $inv): string => $inv->id->toString());
    });

    $handler = new CreateInvoiceHandler($repository);

    $data = new StoreInvoiceData(
        user_id: 1,
        invoice_number: 'INV-0001',
        invoice_date: '2026-04-13',
        bill_to_name: 'Test Client',
        claim_id: null,
        bill_to_address: null,
        bill_to_phone: null,
        bill_to_email: null,
        subtotal: 500.00,
        tax_amount: 50.00,
        balance_due: 550.00,
        claim_number: null,
        policy_number: null,
        insurance_company: null,
        date_of_loss: null,
        date_received: null,
        date_inspected: null,
        date_entered: null,
        price_list_code: null,
        type_of_loss: null,
        notes: null,
        status: 'draft',
        items: [],
    );

    $uuid = $handler->handle($data);

    expect($uuid)->toBeString()
        ->and(strlen($uuid))->toBe(36);
});

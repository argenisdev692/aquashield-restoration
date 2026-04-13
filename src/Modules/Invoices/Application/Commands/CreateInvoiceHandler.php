<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Commands;

use Illuminate\Support\Str;
use Src\Modules\Invoices\Application\DTOs\StoreInvoiceData;
use Src\Modules\Invoices\Domain\Entities\Invoice;
use Src\Modules\Invoices\Domain\Ports\InvoiceRepositoryPort;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceStatus;

final class CreateInvoiceHandler
{
    public function __construct(
        private readonly InvoiceRepositoryPort $repository,
    ) {}

    #[\NoDiscard('UUID of the created invoice must be captured.')]
    public function handle(StoreInvoiceData $data): string
    {
        $uuid = Str::uuid()->toString();

        $invoice = new Invoice(
            id: new InvoiceId($uuid),
            userId: $data->user_id,
            claimId: $data->claim_id,
            invoiceNumber: $data->invoice_number,
            invoiceDate: $data->invoice_date,
            billToName: $data->bill_to_name,
            billToAddress: $data->bill_to_address,
            billToPhone: $data->bill_to_phone,
            billToEmail: $data->bill_to_email,
            subtotal: $data->subtotal,
            taxAmount: $data->tax_amount,
            balanceDue: $data->balance_due,
            claimNumber: $data->claim_number,
            policyNumber: $data->policy_number,
            insuranceCompany: $data->insurance_company,
            dateOfLoss: $data->date_of_loss,
            dateReceived: $data->date_received,
            dateInspected: $data->date_inspected,
            dateEntered: $data->date_entered,
            priceListCode: $data->price_list_code,
            typeOfLoss: $data->type_of_loss,
            notes: $data->notes,
            status: InvoiceStatus::from($data->status),
            pdfUrl: null,
            items: $data->items,
        );

        return $this->repository->save($invoice);
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Application\Commands;

use RuntimeException;
use Src\Modules\Invoices\Application\DTOs\UpdateInvoiceData;
use Src\Modules\Invoices\Domain\Ports\InvoiceRepositoryPort;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceStatus;

final class UpdateInvoiceHandler
{
    public function __construct(
        private readonly InvoiceRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateInvoiceData $data): void
    {
        $invoice = $this->repository->findByUuid($uuid);

        if ($invoice === null) {
            throw new RuntimeException("Invoice [{$uuid}] not found.");
        }

        $invoice->claimId        = $data->claim_id;
        $invoice->invoiceNumber  = $data->invoice_number;
        $invoice->invoiceDate    = $data->invoice_date;
        $invoice->billToName     = $data->bill_to_name;
        $invoice->billToAddress  = $data->bill_to_address;
        $invoice->billToPhone    = $data->bill_to_phone;
        $invoice->billToEmail    = $data->bill_to_email;
        $invoice->subtotal       = $data->subtotal;
        $invoice->taxAmount      = $data->tax_amount;
        $invoice->balanceDue     = $data->balance_due;
        $invoice->claimNumber    = $data->claim_number;
        $invoice->policyNumber   = $data->policy_number;
        $invoice->insuranceCompany = $data->insurance_company;
        $invoice->dateOfLoss     = $data->date_of_loss;
        $invoice->dateReceived   = $data->date_received;
        $invoice->dateInspected  = $data->date_inspected;
        $invoice->dateEntered    = $data->date_entered;
        $invoice->priceListCode  = $data->price_list_code;
        $invoice->typeOfLoss     = $data->type_of_loss;
        $invoice->notes          = $data->notes;
        $invoice->status         = InvoiceStatus::from($data->status);
        $invoice->items          = $data->items;

        $this->repository->update($uuid, $invoice);
    }
}

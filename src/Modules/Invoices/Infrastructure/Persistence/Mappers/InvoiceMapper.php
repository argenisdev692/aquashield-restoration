<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Persistence\Mappers;

use Src\Modules\Invoices\Domain\Entities\Invoice;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceStatus;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;

final class InvoiceMapper
{
    public function toDomain(InvoiceEloquentModel $model): Invoice
    {
        return new Invoice(
            id: new InvoiceId($model->uuid),
            userId: $model->user_id,
            claimId: $model->claim_id,
            invoiceNumber: $model->invoice_number,
            invoiceDate: $model->invoice_date?->format('Y-m-d') ?? '',
            billToName: $model->bill_to_name,
            billToAddress: $model->bill_to_address,
            billToPhone: $model->bill_to_phone,
            billToEmail: $model->bill_to_email,
            subtotal: (float) $model->subtotal,
            taxAmount: (float) $model->tax_amount,
            balanceDue: (float) $model->balance_due,
            claimNumber: $model->claim_number,
            policyNumber: $model->policy_number,
            insuranceCompany: $model->insurance_company,
            dateOfLoss: $model->date_of_loss?->format('Y-m-d'),
            dateReceived: $model->date_received?->format('Y-m-d H:i:s'),
            dateInspected: $model->date_inspected?->format('Y-m-d H:i:s'),
            dateEntered: $model->date_entered?->format('Y-m-d H:i:s'),
            priceListCode: $model->price_list_code,
            typeOfLoss: $model->type_of_loss,
            notes: $model->notes,
            status: InvoiceStatus::from($model->status),
            pdfUrl: $model->pdf_url,
            items: [],
        );
    }

    public function toPersistence(Invoice $invoice): array
    {
        return $invoice
            |> static fn (Invoice $inv): array => [
                'uuid'             => $inv->id->toString(),
                'user_id'         => $inv->userId,
                'claim_id'        => $inv->claimId,
                'invoice_number'  => $inv->invoiceNumber,
                'invoice_date'    => $inv->invoiceDate,
                'bill_to_name'    => $inv->billToName,
                'bill_to_address' => $inv->billToAddress,
                'bill_to_phone'   => $inv->billToPhone,
                'bill_to_email'   => $inv->billToEmail,
                'subtotal'        => $inv->subtotal,
                'tax_amount'      => $inv->taxAmount,
                'balance_due'     => $inv->balanceDue,
                'claim_number'    => $inv->claimNumber,
                'policy_number'   => $inv->policyNumber,
                'insurance_company' => $inv->insuranceCompany,
                'date_of_loss'    => $inv->dateOfLoss,
                'date_received'   => $inv->dateReceived,
                'date_inspected'  => $inv->dateInspected,
                'date_entered'    => $inv->dateEntered,
                'price_list_code' => $inv->priceListCode,
                'type_of_loss'    => $inv->typeOfLoss,
                'notes'           => $inv->notes,
                'status'          => $inv->status->value,
                'pdf_url'         => $inv->pdfUrl,
            ];
    }
}

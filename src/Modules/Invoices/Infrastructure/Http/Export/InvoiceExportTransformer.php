<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Export;

use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;

final class InvoiceExportTransformer
{
    #[\NoDiscard('Excel export rows must be captured by the export pipeline.')]
    public static function forExcel(InvoiceEloquentModel $invoice): array
    {
        return $invoice
            |> static fn (InvoiceEloquentModel $inv): array => [
                $inv->invoice_number,
                $inv->bill_to_name,
                $inv->bill_to_email ?? '—',
                $inv->bill_to_phone ?? '—',
                $inv->status,
                '$' . number_format((float) $inv->subtotal, 2),
                '$' . number_format((float) $inv->tax_amount, 2),
                '$' . number_format((float) $inv->balance_due, 2),
                $inv->claim_number ?? '—',
                $inv->insurance_company ?? '—',
                $inv->invoice_date?->format('F j, Y') ?? '—',
                $inv->deleted_at !== null ? 'Suspended' : 'Active',
                $inv->created_at?->format('F j, Y') ?? '—',
                $inv->deleted_at?->format('F j, Y') ?? '—',
            ];
    }

    #[\NoDiscard('PDF export rows must be captured by the export pipeline.')]
    public static function forPdf(InvoiceEloquentModel $invoice): array
    {
        return $invoice
            |> static fn (InvoiceEloquentModel $inv): array => [
                'invoice_number'    => $inv->invoice_number,
                'bill_to_name'      => $inv->bill_to_name,
                'bill_to_email'     => $inv->bill_to_email ?? '—',
                'bill_to_phone'     => $inv->bill_to_phone ?? '—',
                'status'            => $inv->status,
                'subtotal'          => '$' . number_format((float) $inv->subtotal, 2),
                'tax_amount'        => '$' . number_format((float) $inv->tax_amount, 2),
                'balance_due'       => '$' . number_format((float) $inv->balance_due, 2),
                'claim_number'      => $inv->claim_number ?? '—',
                'insurance_company' => $inv->insurance_company ?? '—',
                'invoice_date'      => $inv->invoice_date?->format('F j, Y') ?? '—',
                'record_status'     => $inv->deleted_at !== null ? 'Suspended' : 'Active',
                'created_at'        => $inv->created_at?->format('F j, Y') ?? '—',
            ];
    }
}

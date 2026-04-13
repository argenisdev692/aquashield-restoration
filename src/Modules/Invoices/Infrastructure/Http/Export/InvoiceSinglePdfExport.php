<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceEloquentModel;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoiceSinglePdfExport
{
    public function download(InvoiceEloquentModel $invoice, string $filename): StreamedResponse
    {
        return Pdf::loadView('exports.pdf.invoice_single', [
            'invoice'     => $invoice,
            'generatedAt' => now()->format('F j, Y H:i'),
        ])
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoicePdfExport
{
    public function download(InvoiceFilterData $filters, string $filename): StreamedResponse
    {
        $rows = InvoiceExportQuery::build($filters)
            ->cursor()
            ->map(fn ($invoice) => InvoiceExportTransformer::forPdf($invoice))
            ->values()
            ->all();

        return Pdf::loadView('exports.pdf.invoices', [
            'rows'        => $rows,
            'generatedAt' => now()->format('F j, Y H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}

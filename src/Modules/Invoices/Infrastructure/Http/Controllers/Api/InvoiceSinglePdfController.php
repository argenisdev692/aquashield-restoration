<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Src\Modules\Invoices\Application\Queries\Contracts\InvoiceReadRepository;
use Src\Modules\Invoices\Infrastructure\Http\Export\InvoiceSinglePdfExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @OA\Get(
 *     path="/invoices/data/admin/{uuid}/invoice-pdf",
 *     tags={"Invoices"},
 *     summary="Generate single invoice PDF (styled invoice document)",
 *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *     @OA\Response(response=200, description="PDF downloaded"),
 *     @OA\Response(response=404, description="Invoice not found"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class InvoiceSinglePdfController extends Controller
{
    public function __construct(
        private readonly InvoiceReadRepository $readRepository,
    ) {}

    public function __invoke(string $uuid): StreamedResponse
    {
        $invoice = $this->readRepository->findEloquentByUuid($uuid);

        if ($invoice === null) {
            abort(404, 'Invoice not found.');
        }

        $filename = 'invoice_' . $invoice->invoice_number . '_' . now()->format('Y_m_d') . '.pdf';

        return (new InvoiceSinglePdfExport())->download($invoice, $filename);
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\Invoices\Application\DTOs\InvoiceFilterData;
use Src\Modules\Invoices\Infrastructure\Http\Export\InvoiceExcelExport;
use Src\Modules\Invoices\Infrastructure\Http\Requests\ExportInvoiceRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @OA\Get(
 *     path="/invoices/data/admin/export",
 *     tags={"Invoices"},
 *     summary="Export invoices to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="invoice_status", in="query", required=false, @OA\Schema(type="string", enum={"draft","sent","paid","cancelled","print_pdf"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class InvoiceExportController extends Controller
{
    public function __invoke(ExportInvoiceRequest $request): BinaryFileResponse|StreamedResponse
    {
        $filters  = InvoiceFilterData::from($request->validated());
        $format   = $request->input('format', 'excel');
        $filename = 'invoices_' . now()->format('Y_m_d_His');

        return match($format) {
            'pdf'   => (new InvoicePdfExport())->download($filters, "{$filename}.pdf"),
            default => Excel::download(new InvoiceExcelExport($filters), "{$filename}.xlsx"),
        };
    }
}

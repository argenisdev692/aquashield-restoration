<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;
use Src\Modules\FilesEsx\Infrastructure\Http\Export\FileEsxExcelExport;
use Src\Modules\FilesEsx\Infrastructure\Http\Export\FileEsxPdfExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class FileEsxExportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/files-esx/export",
     *     tags={"Files ESX"},
     *     summary="Export files ESX (Excel or PDF)",
     *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="uploaded_by", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="File download (Excel or PDF)"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function __invoke(): BinaryFileResponse|Response
    {
        $filters = FileEsxFilterData::from(request()->query());
        $format  = request()->query('format', 'excel');

        if ($format === 'pdf') {
            return (new FileEsxPdfExport($filters))->stream();
        }

        return Excel::download(
            new FileEsxExcelExport($filters),
            'files-esx-report-' . now()->format('Y-m-d') . '.xlsx',
        );
    }
}

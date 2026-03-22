<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\TypeDamages\Application\DTOs\TypeDamageFilterData;
use Src\Modules\TypeDamages\Infrastructure\Http\Export\TypeDamageExcelExport;
use Src\Modules\TypeDamages\Infrastructure\Http\Export\TypeDamagePdfExport;
use Src\Modules\TypeDamages\Infrastructure\Http\Requests\ExportTypeDamageRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/api/type-damages/export",
 *     tags={"Type Damages"},
 *     summary="Export type damages to Excel or PDF",
 *     description="Downloads all type damages matching the given filters as an Excel or PDF file.",
 *     @OA\Parameter(
 *         name="format",
 *         in="query",
 *         required=false,
 *         description="Export format",
 *         @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")
 *     ),
 *     @OA\Parameter(name="search",   in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status",   in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="severity", in="query", required=false, @OA\Schema(type="string", enum={"low","medium","high"})),
 *     @OA\Parameter(name="date_from",in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to",  in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class TypeDamageExportController
{
    public function __invoke(ExportTypeDamageRequest $request): Response|BinaryFileResponse
    {
        $filters = TypeDamageFilterData::from($request->validated());
        $format  = (string) $request->query('format', 'excel');

        return match ($format) {
            'pdf'   => (new TypeDamagePdfExport($filters))->stream(),
            default => Excel::download(
                new TypeDamageExcelExport($filters),
                'type-damages-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}

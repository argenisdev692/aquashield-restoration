<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Users\Application\DTOs\UserFilterDTO;
use Modules\Users\Infrastructure\Http\Export\UserExcelExport;
use Modules\Users\Infrastructure\Http\Export\UserPdfExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * UserExportController
 *
 * @OA\Get(
 *     path="/api/users/admin/export",
 *     tags={"Users"},
 *     summary="Export users",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel", "pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File download"),
 *     security={{"sanctum": {}}}
 * )
 */
final class UserExportController
{
    public function __invoke(Request $request): Response|BinaryFileResponse
    {
        $filters = UserFilterDTO::from($request->query());
        $format = $request->query('format', 'excel');

        if ($format === 'pdf') {
            return (new UserPdfExport($filters))->download();
        }

        return Excel::download(new UserExcelExport($filters), 'users.xlsx');
    }
}

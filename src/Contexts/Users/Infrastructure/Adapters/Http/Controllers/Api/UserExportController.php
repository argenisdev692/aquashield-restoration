<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Infrastructure\Adapters\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Contexts\Users\Application\DTOs\UserFilterDTO;
use Src\Contexts\Users\Infrastructure\Adapters\Http\Export\UserExcelExport;
use Src\Contexts\Users\Infrastructure\Adapters\Http\Export\UserPdfExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class UserExportController extends Controller
{
    public function __invoke(Request $request): Response|BinaryFileResponse
    {
        // Coarse validation or just pass to DTO
        $data = $request->all();
        $filters = new UserFilterDTO(
            search: $data['search'] ?? null,
            dateFrom: $data['dateFrom'] ?? null,
            dateTo: $data['dateTo'] ?? null,
            sortBy: $data['sortBy'] ?? 'created_at',
            sortDir: $data['sortDir'] ?? 'desc',
        );

        $format = $request->query('format', 'excel');

        return match ($format) {
            'excel' => Excel::download(
                new UserExcelExport($filters),
                'users-export-' . now()->format('Y-m-d') . '.xlsx'
            ),
            'pdf' => (new UserPdfExport($filters))->stream(),
            default => response()->json(['error' => 'Invalid format'], 422),
        };
    }
}

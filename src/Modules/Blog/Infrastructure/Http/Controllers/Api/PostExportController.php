<?php

declare(strict_types=1);

namespace Modules\Blog\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Blog\Application\DTOs\PostFilterDTO;
use Modules\Blog\Infrastructure\Http\Export\PostExcelExport;
use Modules\Blog\Infrastructure\Http\Export\PostPdfExport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @OA\Tag(
 *     name="Posts",
 *     description="Blog post management endpoints"
 * )
 */
final class PostExportController
{
    /**
     * Export posts to Excel or PDF.
     *
     * @OA\Get(
     *     path="/posts/data/admin/export",
     *     tags={"Posts"},
     *     summary="Export posts",
     *     description="Export posts list to Excel (default) or PDF format",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="format", in="query", required=false, description="Export format", @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft","published"})),
     *     @OA\Response(response=200, description="File download"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function __invoke(Request $request): mixed
    {
        $format = $request->query('format', 'excel');
        $filters = PostFilterDTO::from($request->all());

        if ($format === 'pdf') {
            return (new PostPdfExport($filters))->stream();
        }

        return Excel::download(new PostExcelExport($filters), 'posts.xlsx');
    }
}

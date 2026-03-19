<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;
use Shared\Infrastructure\Export\SimpleTableExportResponder;
use Src\Modules\CategoryProducts\Application\Commands\BulkDeleteCategoryProductHandler;
use Src\Modules\CategoryProducts\Application\Commands\CreateCategoryProductHandler;
use Src\Modules\CategoryProducts\Application\Commands\DeleteCategoryProductHandler;
use Src\Modules\CategoryProducts\Application\Commands\RestoreCategoryProductHandler;
use Src\Modules\CategoryProducts\Application\Commands\UpdateCategoryProductHandler;
use Src\Modules\CategoryProducts\Application\DTOs\BulkDeleteCategoryProductData;
use Src\Modules\CategoryProducts\Application\DTOs\CategoryProductFilterData;
use Src\Modules\CategoryProducts\Application\DTOs\StoreCategoryProductData;
use Src\Modules\CategoryProducts\Application\DTOs\UpdateCategoryProductData;
use Src\Modules\CategoryProducts\Application\Queries\GetCategoryProductHandler;
use Src\Modules\CategoryProducts\Application\Queries\ListCategoryProductsHandler;
use Src\Modules\CategoryProducts\Infrastructure\Http\Requests\BulkDeleteCategoryProductRequest;
use Src\Modules\CategoryProducts\Infrastructure\Http\Requests\ExportCategoryProductRequest;
use Src\Modules\CategoryProducts\Infrastructure\Http\Requests\StoreCategoryProductRequest;
use Src\Modules\CategoryProducts\Infrastructure\Http\Requests\UpdateCategoryProductRequest;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class CategoryProductController extends Controller
{
    public function index(ListCategoryProductsHandler $handler): JsonResponse
    {
        $categoryProducts = $handler->handle(CategoryProductFilterData::from(request()->query()));

        return response()->json([
            'data' => $categoryProducts->items(),
            'meta' => [
                'current_page' => $categoryProducts->currentPage(),
                'last_page' => $categoryProducts->lastPage(),
                'per_page' => $categoryProducts->perPage(),
                'total' => $categoryProducts->total(),
            ],
        ]);
    }

    public function export(ExportCategoryProductRequest $request, SimpleTableExportResponder $exportResponder): Response|BinaryFileResponse
    {
        $validated = $request->validated();
        $filters = CategoryProductFilterData::from($validated);
        $rows = $this->buildExportQuery($filters)->get()->map(
            static fn (CategoryProductEloquentModel $categoryProduct): array => [
                $categoryProduct->category_product_name,
                $categoryProduct->created_at?->format('Y-m-d H:i:s') ?? '-',
                $categoryProduct->deleted_at?->format('Y-m-d H:i:s') ?? '-',
            ],
        )->all();

        return $exportResponder->download(
            $request,
            'Category Products Report',
            'category-products-' . now()->format('Y-m-d'),
            ['Category Product', 'Created At', 'Deleted At'],
            $rows,
        );
    }

    public function show(string $uuid, GetCategoryProductHandler $handler): JsonResponse
    {
        $categoryProduct = $handler->handle($uuid);

        if ($categoryProduct === null) {
            return response()->json(['message' => 'Category product not found.'], 404);
        }

        return response()->json($categoryProduct);
    }

    public function store(StoreCategoryProductRequest $request, CreateCategoryProductHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreCategoryProductData::from($request->validated()));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Category product created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCategoryProductRequest $request, UpdateCategoryProductHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateCategoryProductData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Category product updated successfully.']);
    }

    public function destroy(string $uuid, DeleteCategoryProductHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Category product deleted successfully.']);
    }

    public function restore(string $uuid, RestoreCategoryProductHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Category product restored successfully.']);
    }

    public function bulkDelete(BulkDeleteCategoryProductRequest $request, BulkDeleteCategoryProductHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteCategoryProductData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} category product record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }

    private function buildExportQuery(CategoryProductFilterData $filters): Builder
    {
        return CategoryProductEloquentModel::query()
            ->withTrashed()
            ->select([
                'category_product_name',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static fn (Builder $builder, string $search): Builder => $builder->where('category_product_name', 'like', "%{$search}%"))
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderBy('category_product_name')
            ->orderByDesc('created_at');
    }
}

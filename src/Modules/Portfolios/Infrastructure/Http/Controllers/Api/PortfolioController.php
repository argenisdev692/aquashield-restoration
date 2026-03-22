<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Src\Modules\Portfolios\Application\Commands\BulkDeletePortfolioHandler;
use Src\Modules\Portfolios\Application\Commands\CreatePortfolioHandler;
use Src\Modules\Portfolios\Application\Commands\DeletePortfolioHandler;
use Src\Modules\Portfolios\Application\Commands\RestorePortfolioHandler;
use Src\Modules\Portfolios\Application\Commands\UpdatePortfolioHandler;
use Src\Modules\Portfolios\Application\DTOs\BulkDeletePortfolioData;
use Src\Modules\Portfolios\Application\DTOs\PortfolioFilterData;
use Src\Modules\Portfolios\Application\DTOs\StorePortfolioData;
use Src\Modules\Portfolios\Application\DTOs\UpdatePortfolioData;
use Src\Modules\Portfolios\Application\Queries\GetPortfolioHandler;
use Src\Modules\Portfolios\Application\Queries\ListPortfoliosHandler;
use Src\Modules\Portfolios\Infrastructure\Http\Requests\BulkDeletePortfolioRequest;
use Src\Modules\Portfolios\Infrastructure\Http\Requests\StorePortfolioRequest;
use Src\Modules\Portfolios\Infrastructure\Http\Requests\UpdatePortfolioRequest;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioImageEloquentModel;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

/**
 * @OA\Tag(name="Portfolios", description="Portfolios CRUD operations with image management")
 */
final class PortfolioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/portfolios",
     *     tags={"Portfolios"},
     *     summary="List portfolios",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="project_type_uuid", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated portfolios list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListPortfoliosHandler $handler): JsonResponse
    {
        $portfolios = $handler->handle(PortfolioFilterData::from(request()->query()));

        return response()->json([
            'data' => $portfolios->items(),
            'meta' => [
                'current_page' => $portfolios->currentPage(),
                'last_page'    => $portfolios->lastPage(),
                'per_page'     => $portfolios->perPage(),
                'total'        => $portfolios->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/portfolios/project-types",
     *     tags={"Portfolios"},
     *     summary="List available project types for portfolio filters",
     *     @OA\Response(
     *         response=200,
     *         description="Project types list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", format="uuid"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="service_category_name", type="string", nullable=true)
     *             ))
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function projectTypes(): JsonResponse
    {
        $projectTypes = ProjectTypeEloquentModel::query()
            ->whereNull('deleted_at')
            ->with('serviceCategory:id,uuid,category')
            ->orderBy('title')
            ->get(['id', 'uuid', 'title', 'service_category_id']);

        $data = $projectTypes->map(static fn ($pt) => [
            'uuid'                  => $pt->uuid,
            'title'                 => $pt->title,
            'service_category_name' => $pt->serviceCategory?->category,
        ]);

        return response()->json(['data' => $data]);
    }

    /**
     * @OA\Get(
     *     path="/api/portfolios/{uuid}",
     *     tags={"Portfolios"},
     *     summary="Show portfolio",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Portfolio detail", @OA\JsonContent(type="object")),
     *     @OA\Response(response=404, description="Portfolio not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetPortfolioHandler $handler): JsonResponse
    {
        $portfolio = $handler->handle($uuid);

        if ($portfolio === null) {
            return response()->json(['message' => 'Portfolio not found.'], 404);
        }

        return response()->json($portfolio);
    }

    /**
     * @OA\Post(
     *     path="/api/portfolios",
     *     tags={"Portfolios"},
     *     summary="Create portfolio",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="project_type_uuid", type="string", format="uuid", nullable=true)
     *     )),
     *     @OA\Response(
     *         response=201,
     *         description="Portfolio created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation or business rule error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StorePortfolioRequest $request, CreatePortfolioHandler $handler): JsonResponse
    {
        try {
            $uuid = $handler->handle(StorePortfolioData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Portfolio created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/portfolios/{uuid}",
     *     tags={"Portfolios"},
     *     summary="Update portfolio",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="project_type_uuid", type="string", format="uuid", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Portfolio updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Portfolio not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdatePortfolioRequest $request, UpdatePortfolioHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdatePortfolioData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Portfolio updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/portfolios/{uuid}",
     *     tags={"Portfolios"},
     *     summary="Soft-delete portfolio",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Portfolio deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeletePortfolioHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Portfolio deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/portfolios/{uuid}/restore",
     *     tags={"Portfolios"},
     *     summary="Restore portfolio",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Portfolio restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestorePortfolioHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Portfolio restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/portfolios/bulk-delete",
     *     tags={"Portfolios"},
     *     summary="Bulk delete portfolios",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Portfolios deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeletePortfolioRequest $request, BulkDeletePortfolioHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeletePortfolioData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} portfolio record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/portfolios/{uuid}/images",
     *     tags={"Portfolios"},
     *     summary="Upload image to portfolio",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Image uploaded",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Portfolio not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function uploadImage(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $portfolio = PortfolioEloquentModel::withTrashed()->where('uuid', $uuid)->first();

        if ($portfolio === null) {
            return response()->json(['message' => 'Portfolio not found.'], 404);
        }

        $file      = $request->file('image');
        $imageUuid = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $path      = "portfolios/{$uuid}/{$imageUuid}.{$extension}";

        Storage::disk('r2')->put($path, $file->get(), 'public');

        $maxOrder = $portfolio->images()->max('order') ?? 0;

        $image = new PortfolioImageEloquentModel();
        $image->uuid         = $imageUuid;
        $image->portfolio_id = $portfolio->id;
        $image->path         = $path;
        $image->order        = $maxOrder + 1;
        $image->save();

        return response()->json([
            'uuid'  => $imageUuid,
            'path'  => $path,
            'order' => $image->order,
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/portfolios/{uuid}/images/{imageUuid}",
     *     tags={"Portfolios"},
     *     summary="Delete portfolio image",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="imageUuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Image deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Image not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function deleteImage(string $portfolioUuid, string $imageUuid): JsonResponse
    {
        $image = PortfolioImageEloquentModel::query()
            ->whereHas('portfolio', static fn ($q) => $q->where('uuid', $portfolioUuid))
            ->where('uuid', $imageUuid)
            ->first();

        if ($image === null) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        Storage::disk('r2')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully.']);
    }

    /**
     * @OA\Put(
     *     path="/api/portfolios/{uuid}/images/reorder",
     *     tags={"Portfolios"},
     *     summary="Reorder portfolio images",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="images", type="array", @OA\Items(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="order", type="integer")
     *         ))
     *     )),
     *     @OA\Response(response=200, description="Images reordered", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Portfolio not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function reorderImages(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'images'         => ['required', 'array'],
            'images.*.uuid'  => ['required', 'string', 'uuid'],
            'images.*.order' => ['required', 'integer', 'min:0'],
        ]);

        $portfolio = PortfolioEloquentModel::withTrashed()->where('uuid', $uuid)->first();

        if ($portfolio === null) {
            return response()->json(['message' => 'Portfolio not found.'], 404);
        }

        foreach ($request->input('images') as $item) {
            PortfolioImageEloquentModel::query()
                ->where('uuid', $item['uuid'])
                ->where('portfolio_id', $portfolio->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Images reordered successfully.']);
    }
}

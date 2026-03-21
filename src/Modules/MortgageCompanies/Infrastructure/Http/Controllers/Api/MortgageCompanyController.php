<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Modules\MortgageCompanies\Application\Commands\BulkDeleteMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Commands\CreateMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Commands\DeleteMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Commands\RestoreMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Commands\UpdateMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\DTOs\BulkDeleteMortgageCompanyData;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;
use Modules\MortgageCompanies\Application\DTOs\StoreMortgageCompanyData;
use Modules\MortgageCompanies\Application\DTOs\UpdateMortgageCompanyData;
use Modules\MortgageCompanies\Application\Queries\GetMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Queries\ListMortgageCompaniesHandler;
use Modules\MortgageCompanies\Infrastructure\Http\Requests\BulkDeleteMortgageCompanyRequest;
use Modules\MortgageCompanies\Infrastructure\Http\Requests\StoreMortgageCompanyRequest;
use Modules\MortgageCompanies\Infrastructure\Http\Requests\UpdateMortgageCompanyRequest;

/**
 * @OA\Tag(name="Mortgage Companies", description="Mortgage companies CRUD operations")
 */
final class MortgageCompanyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/mortgage-companies",
     *     tags={"Mortgage Companies"},
     *     summary="List mortgage companies",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active", "deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated mortgage companies list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MortgageCompanyListReadModel")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListMortgageCompaniesHandler $handler): JsonResponse
    {
        $mortgageCompanies = $handler->handle(MortgageCompanyFilterData::from(request()->query()));

        return response()->json([
            'data' => $mortgageCompanies->items(),
            'meta' => [
                'current_page' => $mortgageCompanies->currentPage(),
                'last_page'    => $mortgageCompanies->lastPage(),
                'per_page'     => $mortgageCompanies->perPage(),
                'total'        => $mortgageCompanies->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/mortgage-companies/{uuid}",
     *     tags={"Mortgage Companies"},
     *     summary="Show mortgage company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Mortgage company detail",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/MortgageCompanyReadModel"))
     *     ),
     *     @OA\Response(response=404, description="Mortgage company not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetMortgageCompanyHandler $handler): JsonResponse
    {
        $mortgageCompany = $handler->handle($uuid);

        if ($mortgageCompany === null) {
            return response()->json(['message' => 'Mortgage company not found.'], 404);
        }

        return response()->json(['data' => $mortgageCompany]);
    }

    /**
     * @OA\Post(
     *     path="/api/mortgage-companies",
     *     tags={"Mortgage Companies"},
     *     summary="Create mortgage company",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreMortgageCompanyData")),
     *     @OA\Response(
     *         response=201,
     *         description="Mortgage company created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreMortgageCompanyRequest $request, CreateMortgageCompanyHandler $handler): JsonResponse
    {
        $payload = array_merge($request->validated(), [
            'user_id' => (int) $request->user()->getAuthIdentifier(),
        ]);

        $uuid = $handler->handle(StoreMortgageCompanyData::from($payload));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Mortgage company created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/mortgage-companies/{uuid}",
     *     tags={"Mortgage Companies"},
     *     summary="Update mortgage company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateMortgageCompanyData")),
     *     @OA\Response(
     *         response=200,
     *         description="Mortgage company updated",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=404, description="Mortgage company not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateMortgageCompanyRequest $request, UpdateMortgageCompanyHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateMortgageCompanyData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Mortgage company updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/mortgage-companies/{uuid}",
     *     tags={"Mortgage Companies"},
     *     summary="Delete mortgage company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Mortgage company deleted",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteMortgageCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Mortgage company deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/mortgage-companies/{uuid}/restore",
     *     tags={"Mortgage Companies"},
     *     summary="Restore mortgage company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Mortgage company restored",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreMortgageCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Mortgage company restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/mortgage-companies/bulk-delete",
     *     tags={"Mortgage Companies"},
     *     summary="Bulk delete mortgage companies",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BulkDeleteMortgageCompanyData")),
     *     @OA\Response(
     *         response=200,
     *         description="Mortgage companies bulk deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteMortgageCompanyRequest $request, BulkDeleteMortgageCompanyHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteMortgageCompanyData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} mortgage company record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}

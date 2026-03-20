<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\InsuranceCompanies\Application\Commands\BulkDeleteInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\DTOs\BulkDeleteInsuranceCompanyData;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;
use Modules\InsuranceCompanies\Application\DTOs\StoreInsuranceCompanyData;
use Modules\InsuranceCompanies\Application\DTOs\UpdateInsuranceCompanyData;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompaniesHandler;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\BulkDeleteInsuranceCompanyRequest;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\StoreInsuranceCompanyRequest;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\UpdateInsuranceCompanyRequest;
use RuntimeException;

/**
 * @OA\Tag(name="Insurance Companies", description="Insurance companies CRUD operations")
 */
final class InsuranceCompanyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/insurance-companies",
     *     tags={"Insurance Companies"},
     *     summary="List insurance companies",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active", "deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated insurance companies list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/InsuranceCompanyReadModel")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="currentPage", type="integer"),
     *                 @OA\Property(property="lastPage", type="integer"),
     *                 @OA\Property(property="perPage", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListInsuranceCompaniesHandler $handler): JsonResponse
    {
        $companies = $handler->handle(InsuranceCompanyFilterData::from(request()->query()));

        return response()->json([
            'data' => $companies->items(),
            'meta' => [
                'currentPage' => $companies->currentPage(),
                'lastPage' => $companies->lastPage(),
                'perPage' => $companies->perPage(),
                'total' => $companies->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/insurance-companies/{uuid}",
     *     tags={"Insurance Companies"},
     *     summary="Show insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance company detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/InsuranceCompanyReadModel")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Insurance company not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetInsuranceCompanyHandler $handler): JsonResponse
    {
        $insuranceCompany = $handler->handle($uuid);

        if ($insuranceCompany === null) {
            return response()->json(['message' => 'Insurance company not found.'], 404);
        }

        return response()->json([
            'data' => $insuranceCompany,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/insurance-companies",
     *     tags={"Insurance Companies"},
     *     summary="Create insurance company",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreInsuranceCompanyData")),
     *     @OA\Response(
     *         response=201,
     *         description="Insurance company created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreInsuranceCompanyRequest $request, CreateInsuranceCompanyHandler $handler): JsonResponse
    {
        $payload = StoreInsuranceCompanyData::from([
            ...$request->validated(),
            'user_id' => (int) $request->user()->id,
        ]);

        $uuid = $handler->handle($payload);

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Insurance company created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/insurance-companies/{uuid}",
     *     tags={"Insurance Companies"},
     *     summary="Update insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateInsuranceCompanyData")),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance company updated",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=404, description="Insurance company not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateInsuranceCompanyRequest $request, UpdateInsuranceCompanyHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateInsuranceCompanyData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Insurance company updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/insurance-companies/{uuid}",
     *     tags={"Insurance Companies"},
     *     summary="Delete insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance company deleted",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteInsuranceCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Insurance company deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/insurance-companies/{uuid}/restore",
     *     tags={"Insurance Companies"},
     *     summary="Restore insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance company restored",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreInsuranceCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Insurance company restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/insurance-companies/bulk-delete",
     *     tags={"Insurance Companies"},
     *     summary="Bulk delete insurance companies",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BulkDeleteInsuranceCompanyData")),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance companies deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteInsuranceCompanyRequest $request, BulkDeleteInsuranceCompanyHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteInsuranceCompanyData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} insurance company record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany\CreateInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany\CreateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompany\DeleteInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompany\DeleteInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompany\RestoreInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompany\RestoreInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompany\UpdateInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompany\UpdateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\DTOs\CreateInsuranceCompanyDTO;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterDTO;
use Modules\InsuranceCompanies\Application\DTOs\UpdateInsuranceCompanyDTO;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany\GetInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany\GetInsuranceCompanyQuery;
use Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies\ListInsuranceCompaniesHandler;
use Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies\ListInsuranceCompaniesQuery;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\CreateInsuranceCompanyRequest;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\UpdateInsuranceCompanyRequest;
use Modules\InsuranceCompanies\Infrastructure\Http\Resources\InsuranceCompanyResource;

/**
 * @OA\Tag(name="Insurance Companies", description="Insurance Companies CRUD operations")
 */
final class InsuranceCompanyController
{
    public function __construct(
        private readonly CreateInsuranceCompanyHandler $createHandler,
        private readonly UpdateInsuranceCompanyHandler $updateHandler,
        private readonly DeleteInsuranceCompanyHandler $deleteHandler,
        private readonly RestoreInsuranceCompanyHandler $restoreHandler,
        private readonly ListInsuranceCompaniesHandler $listHandler,
        private readonly GetInsuranceCompanyHandler $getHandler,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/insurance-companies",
     *     tags={"Insurance Companies"},
     *     summary="List insurance companies",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="perPage", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated list of insurance companies"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = InsuranceCompanyFilterDTO::from($request->all());
        $result = $this->listHandler->handle(new ListInsuranceCompaniesQuery($filters));

        return response()->json([
            'data' => array_map(fn($item) => new InsuranceCompanyResource($item), $result['data']),
            'meta' => [
                'total' => $result['total'],
                'perPage' => $result['perPage'],
                'currentPage' => $result['currentPage'],
                'lastPage' => $result['lastPage'],
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/insurance-companies/{uuid}",
     *     tags={"Insurance Companies"},
     *     summary="Get a single insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Insurance company details"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid): JsonResponse
    {
        $insuranceCompany = $this->getHandler->handle(new GetInsuranceCompanyQuery($uuid));

        return response()->json([
            'data' => new InsuranceCompanyResource($insuranceCompany),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/insurance-companies",
     *     tags={"Insurance Companies"},
     *     summary="Create a new insurance company",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CreateInsuranceCompanyDTO")),
     *     @OA\Response(response=201, description="Insurance company created"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(CreateInsuranceCompanyRequest $request): JsonResponse
    {
        $dto = new CreateInsuranceCompanyDTO(
            insuranceCompanyName: $request->insurance_company_name,
            address: $request->address,
            phone: $request->phone,
            email: $request->email,
            website: $request->website,
            userId: $request->user_id ?? auth()->id(),
        );

        $insuranceCompany = $this->createHandler->handle(new CreateInsuranceCompanyCommand($dto));

        return response()->json([
            'data' => new InsuranceCompanyResource($insuranceCompany),
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/insurance-companies/{uuid}",
     *     tags={"Insurance Companies"},
     *     summary="Update an insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateInsuranceCompanyDTO")),
     *     @OA\Response(response=200, description="Insurance company updated"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(UpdateInsuranceCompanyRequest $request, string $uuid): JsonResponse
    {
        $dto = new UpdateInsuranceCompanyDTO(
            insuranceCompanyName: $request->insurance_company_name,
            address: $request->address,
            phone: $request->phone,
            email: $request->email,
            website: $request->website,
        );

        $insuranceCompany = $this->updateHandler->handle(new UpdateInsuranceCompanyCommand($uuid, $dto));

        return response()->json([
            'data' => new InsuranceCompanyResource($insuranceCompany),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/insurance-companies/{uuid}",
     *     tags={"Insurance Companies"},
     *     summary="Soft-delete an insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="Insurance company deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteInsuranceCompanyCommand($uuid));

        return response()->json(null, 204);
    }

    /**
     * @OA\Patch(
     *     path="/api/insurance-companies/{uuid}/restore",
     *     tags={"Insurance Companies"},
     *     summary="Restore a soft-deleted insurance company",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Insurance company restored"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestoreInsuranceCompanyCommand($uuid));

        return response()->json(['message' => 'Insurance company restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/insurance-companies/bulk-delete",
     *     tags={"Insurance Companies"},
     *     summary="Bulk soft-delete insurance companies",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Insurance companies deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $uuids = $request->input('uuids', []);

        foreach ($uuids as $uuid) {
            $this->deleteHandler->handle(new DeleteInsuranceCompanyCommand($uuid));
        }

        return response()->json(['message' => count($uuids) . ' insurance companies deleted successfully.']);
    }
}

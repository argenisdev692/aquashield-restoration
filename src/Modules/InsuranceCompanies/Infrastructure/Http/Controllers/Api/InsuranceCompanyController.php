<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany\CreateInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\CreateInsuranceCompany\CreateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompany\DeleteInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\DeleteInsuranceCompany\DeleteInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompany\RestoreInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\RestoreInsuranceCompany\RestoreInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompany\UpdateInsuranceCompanyCommand;
use Modules\InsuranceCompanies\Application\Commands\UpdateInsuranceCompany\UpdateInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyDTO;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany\GetInsuranceCompanyHandler;
use Modules\InsuranceCompanies\Application\Queries\GetInsuranceCompany\GetInsuranceCompanyQuery;
use Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies\ListInsuranceCompaniesHandler;
use Modules\InsuranceCompanies\Application\Queries\ListInsuranceCompanies\ListInsuranceCompaniesQuery;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\CreateInsuranceCompanyRequest;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\UpdateInsuranceCompanyRequest;
use Modules\InsuranceCompanies\Infrastructure\Http\Resources\InsuranceCompanyResource;
use Illuminate\Http\Request;

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

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $result = $this->listHandler->handle(new ListInsuranceCompaniesQuery($filters));

        return response()->json([
            'data' => array_map(fn($item) => new InsuranceCompanyResource($item), $result['data']),
            'total' => $result['total'],
            'perPage' => $result['perPage'],
            'currentPage' => $result['currentPage'],
            'lastPage' => $result['lastPage'],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $insuranceCompany = $this->getHandler->handle(new GetInsuranceCompanyQuery($uuid));

        return response()->json([
            'data' => new InsuranceCompanyResource($insuranceCompany),
        ]);
    }

    public function store(CreateInsuranceCompanyRequest $request): JsonResponse
    {
        $dto = new InsuranceCompanyDTO(
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

    public function update(UpdateInsuranceCompanyRequest $request, string $uuid): JsonResponse
    {
        $dto = new InsuranceCompanyDTO(
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

    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteInsuranceCompanyCommand($uuid));

        return response()->json(null, 204);
    }

    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestoreInsuranceCompanyCommand($uuid));

        return response()->json(['message' => 'Insurance company restored successfully.']);
    }
}

<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\AllianceCompanies\Application\Commands\CreateAllianceCompany\CreateAllianceCompanyCommand;
use Modules\AllianceCompanies\Application\Commands\CreateAllianceCompany\CreateAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Commands\DeleteAllianceCompany\DeleteAllianceCompanyCommand;
use Modules\AllianceCompanies\Application\Commands\DeleteAllianceCompany\DeleteAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Commands\RestoreAllianceCompany\RestoreAllianceCompanyCommand;
use Modules\AllianceCompanies\Application\Commands\RestoreAllianceCompany\RestoreAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Commands\UpdateAllianceCompany\UpdateAllianceCompanyCommand;
use Modules\AllianceCompanies\Application\Commands\UpdateAllianceCompany\UpdateAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\DTOs\AllianceCompanyDTO;
use Modules\AllianceCompanies\Application\Queries\GetAllianceCompany\GetAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Queries\GetAllianceCompany\GetAllianceCompanyQuery;
use Modules\AllianceCompanies\Application\Queries\ListAllianceCompanies\ListAllianceCompaniesHandler;
use Modules\AllianceCompanies\Application\Queries\ListAllianceCompanies\ListAllianceCompaniesQuery;
use Modules\AllianceCompanies\Infrastructure\Http\Requests\CreateAllianceCompanyRequest;
use Modules\AllianceCompanies\Infrastructure\Http\Requests\UpdateAllianceCompanyRequest;
use Modules\AllianceCompanies\Infrastructure\Http\Resources\AllianceCompanyResource;
use Illuminate\Http\Request;

final class AllianceCompanyController
{
    public function __construct(
        private readonly CreateAllianceCompanyHandler $createHandler,
        private readonly UpdateAllianceCompanyHandler $updateHandler,
        private readonly DeleteAllianceCompanyHandler $deleteHandler,
        private readonly RestoreAllianceCompanyHandler $restoreHandler,
        private readonly ListAllianceCompaniesHandler $listHandler,
        private readonly GetAllianceCompanyHandler $getHandler,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $result = $this->listHandler->handle(new ListAllianceCompaniesQuery($filters));

        return response()->json([
            'data' => array_map(fn($item) => new AllianceCompanyResource($item), $result['data']),
            'meta' => [
                'total' => $result['total'],
                'perPage' => $result['perPage'],
                'currentPage' => $result['currentPage'],
                'lastPage' => $result['lastPage'],
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $AllianceCompany = $this->getHandler->handle(new GetAllianceCompanyQuery($uuid));

        return response()->json([
            'data' => new AllianceCompanyResource($AllianceCompany),
        ]);
    }

    public function store(CreateAllianceCompanyRequest $request): JsonResponse
    {
        $dto = new AllianceCompanyDTO(
            AllianceCompanyName: $request->alliance_company_name,
            address: $request->address,
            phone: $request->phone,
            email: $request->email,
            website: $request->website,
            userId: $request->user_id ?? auth()->id(),
        );

        $AllianceCompany = $this->createHandler->handle(new CreateAllianceCompanyCommand($dto));

        return response()->json([
            'data' => new AllianceCompanyResource($AllianceCompany),
        ], 201);
    }

    public function update(UpdateAllianceCompanyRequest $request, string $uuid): JsonResponse
    {
        $dto = new AllianceCompanyDTO(
            AllianceCompanyName: $request->alliance_company_name,
            address: $request->address,
            phone: $request->phone,
            email: $request->email,
            website: $request->website,
        );

        $AllianceCompany = $this->updateHandler->handle(new UpdateAllianceCompanyCommand($uuid, $dto));

        return response()->json([
            'data' => new AllianceCompanyResource($AllianceCompany),
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteAllianceCompanyCommand($uuid));

        return response()->json(null, 204);
    }

    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestoreAllianceCompanyCommand($uuid));

        return response()->json(['message' => 'Alliance company restored successfully.']);
    }
}

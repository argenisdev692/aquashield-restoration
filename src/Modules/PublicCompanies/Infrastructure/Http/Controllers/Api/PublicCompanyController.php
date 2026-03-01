<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\PublicCompanies\Application\Commands\CreatePublicCompany\CreatePublicCompanyCommand;
use Modules\PublicCompanies\Application\Commands\CreatePublicCompany\CreatePublicCompanyHandler;
use Modules\PublicCompanies\Application\Commands\DeletePublicCompany\DeletePublicCompanyCommand;
use Modules\PublicCompanies\Application\Commands\DeletePublicCompany\DeletePublicCompanyHandler;
use Modules\PublicCompanies\Application\Commands\RestorePublicCompany\RestorePublicCompanyCommand;
use Modules\PublicCompanies\Application\Commands\RestorePublicCompany\RestorePublicCompanyHandler;
use Modules\PublicCompanies\Application\Commands\UpdatePublicCompany\UpdatePublicCompanyCommand;
use Modules\PublicCompanies\Application\Commands\UpdatePublicCompany\UpdatePublicCompanyHandler;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyDTO;
use Modules\PublicCompanies\Application\Queries\GetPublicCompany\GetPublicCompanyHandler;
use Modules\PublicCompanies\Application\Queries\GetPublicCompany\GetPublicCompanyQuery;
use Modules\PublicCompanies\Application\Queries\ListPublicCompanies\ListPublicCompaniesHandler;
use Modules\PublicCompanies\Application\Queries\ListPublicCompanies\ListPublicCompaniesQuery;
use Modules\PublicCompanies\Infrastructure\Http\Requests\CreatePublicCompanyRequest;
use Modules\PublicCompanies\Infrastructure\Http\Requests\UpdatePublicCompanyRequest;
use Modules\PublicCompanies\Infrastructure\Http\Resources\PublicCompanyResource;
use Illuminate\Http\Request;

final class PublicCompanyController
{
    public function __construct(
        private readonly CreatePublicCompanyHandler $createHandler,
        private readonly UpdatePublicCompanyHandler $updateHandler,
        private readonly DeletePublicCompanyHandler $deleteHandler,
        private readonly RestorePublicCompanyHandler $restoreHandler,
        private readonly ListPublicCompaniesHandler $listHandler,
        private readonly GetPublicCompanyHandler $getHandler,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $result = $this->listHandler->handle(new ListPublicCompaniesQuery($filters));

        return response()->json([
            'data' => array_map(fn($item) => new PublicCompanyResource($item), $result['data']),
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
        $PublicCompany = $this->getHandler->handle(new GetPublicCompanyQuery($uuid));

        return response()->json([
            'data' => new PublicCompanyResource($PublicCompany),
        ]);
    }

    public function store(CreatePublicCompanyRequest $request): JsonResponse
    {
        $dto = new PublicCompanyDTO(
            PublicCompanyName: $request->public_company_name,
            address: $request->address,
            phone: $request->phone,
            email: $request->email,
            website: $request->website,
            unit: $request->unit,
            userId: $request->user_id ?? auth()->id(),
        );

        $PublicCompany = $this->createHandler->handle(new CreatePublicCompanyCommand($dto));

        return response()->json([
            'data' => new PublicCompanyResource($PublicCompany),
        ], 201);
    }

    public function update(UpdatePublicCompanyRequest $request, string $uuid): JsonResponse
    {
        $dto = new PublicCompanyDTO(
            PublicCompanyName: $request->public_company_name,
            address: $request->address,
            phone: $request->phone,
            email: $request->email,
            website: $request->website,
            unit: $request->unit,
        );

        $PublicCompany = $this->updateHandler->handle(new UpdatePublicCompanyCommand($uuid, $dto));

        return response()->json([
            'data' => new PublicCompanyResource($PublicCompany),
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeletePublicCompanyCommand($uuid));

        return response()->json(null, 204);
    }

    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestorePublicCompanyCommand($uuid));

        return response()->json(['message' => 'Public company restored successfully.']);
    }
}

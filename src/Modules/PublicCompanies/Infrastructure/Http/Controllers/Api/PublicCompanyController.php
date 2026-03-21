<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\PublicCompanies\Application\Commands\CreatePublicCompanyHandler;
use Modules\PublicCompanies\Application\Commands\DeletePublicCompanyHandler;
use Modules\PublicCompanies\Application\Commands\RestorePublicCompanyHandler;
use Modules\PublicCompanies\Application\Commands\UpdatePublicCompanyHandler;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;
use Modules\PublicCompanies\Application\DTOs\StorePublicCompanyData;
use Modules\PublicCompanies\Application\DTOs\UpdatePublicCompanyData;
use Modules\PublicCompanies\Application\Queries\GetPublicCompanyHandler;
use Modules\PublicCompanies\Application\Queries\ListPublicCompaniesHandler;
use Modules\PublicCompanies\Infrastructure\Http\Requests\StorePublicCompanyRequest;
use Modules\PublicCompanies\Infrastructure\Http\Requests\UpdatePublicCompanyRequest;
use RuntimeException;

final class PublicCompanyController extends Controller
{
    public function index(ListPublicCompaniesHandler $handler): JsonResponse
    {
        $companies = $handler->handle(PublicCompanyFilterData::from(request()->query()));

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

    public function show(string $uuid, GetPublicCompanyHandler $handler): JsonResponse
    {
        $publicCompany = $handler->handle($uuid);

        if ($publicCompany === null) {
            return response()->json(['message' => 'Public company not found.'], 404);
        }

        return response()->json([
            'data' => $publicCompany,
        ]);
    }

    public function store(StorePublicCompanyRequest $request, CreatePublicCompanyHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StorePublicCompanyData::from([
            ...$request->validated(),
            'user_id' => (int) $request->user()->id,
        ]));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Public company created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdatePublicCompanyRequest $request, UpdatePublicCompanyHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdatePublicCompanyData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Public company updated successfully.']);
    }

    public function destroy(string $uuid, DeletePublicCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Public company deleted successfully.']);
    }

    public function restore(string $uuid, RestorePublicCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Public company restored successfully.']);
    }
}

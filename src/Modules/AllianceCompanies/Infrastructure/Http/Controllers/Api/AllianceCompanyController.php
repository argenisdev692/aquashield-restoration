<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Modules\AllianceCompanies\Application\Commands\BulkDeleteAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Commands\CreateAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Commands\DeleteAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Commands\RestoreAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Commands\UpdateAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\DTOs\AllianceCompanyFilterData;
use Modules\AllianceCompanies\Application\DTOs\BulkDeleteAllianceCompanyData;
use Modules\AllianceCompanies\Application\DTOs\StoreAllianceCompanyData;
use Modules\AllianceCompanies\Application\DTOs\UpdateAllianceCompanyData;
use Modules\AllianceCompanies\Application\Queries\GetAllianceCompanyHandler;
use Modules\AllianceCompanies\Application\Queries\ListAllianceCompaniesHandler;
use Modules\AllianceCompanies\Infrastructure\Http\Requests\BulkDeleteAllianceCompanyRequest;
use Modules\AllianceCompanies\Infrastructure\Http\Requests\StoreAllianceCompanyRequest;
use Modules\AllianceCompanies\Infrastructure\Http\Requests\UpdateAllianceCompanyRequest;

final class AllianceCompanyController extends Controller
{
    public function index(ListAllianceCompaniesHandler $handler): JsonResponse
    {
        $allianceCompanies = $handler->handle(AllianceCompanyFilterData::from(request()->query()));

        return response()->json([
            'data' => array_map(
                static fn ($allianceCompany): array => $allianceCompany->toArray(),
                $allianceCompanies->items(),
            ),
            'meta' => [
                'current_page' => $allianceCompanies->currentPage(),
                'last_page' => $allianceCompanies->lastPage(),
                'per_page' => $allianceCompanies->perPage(),
                'total' => $allianceCompanies->total(),
            ],
        ]);
    }

    public function show(string $uuid, GetAllianceCompanyHandler $handler): JsonResponse
    {
        $allianceCompany = $handler->handle($uuid);

        if ($allianceCompany === null) {
            return response()->json(['message' => 'Alliance company not found.'], 404);
        }

        return response()->json($allianceCompany->toArray());
    }

    public function store(StoreAllianceCompanyRequest $request, CreateAllianceCompanyHandler $handler): JsonResponse
    {
        $payload = array_merge($request->validated(), [
            'user_id' => (int) $request->user()->getAuthIdentifier(),
        ]);

        $uuid = $handler->handle(StoreAllianceCompanyData::from($payload));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Alliance company created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateAllianceCompanyRequest $request, UpdateAllianceCompanyHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateAllianceCompanyData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json([
            'message' => 'Alliance company updated successfully.',
        ]);
    }

    public function destroy(string $uuid, DeleteAllianceCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json([
            'message' => 'Alliance company deleted successfully.',
        ]);
    }

    public function restore(string $uuid, RestoreAllianceCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json([
            'message' => 'Alliance company restored successfully.',
        ]);
    }

    public function bulkDelete(BulkDeleteAllianceCompanyRequest $request, BulkDeleteAllianceCompanyHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteAllianceCompanyData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} alliance company record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}

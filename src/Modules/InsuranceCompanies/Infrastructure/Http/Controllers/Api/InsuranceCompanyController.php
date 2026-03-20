<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
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

final class InsuranceCompanyController extends Controller
{
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

    public function update(string $uuid, UpdateInsuranceCompanyRequest $request, UpdateInsuranceCompanyHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateInsuranceCompanyData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Insurance company updated successfully.']);
    }

    public function destroy(string $uuid, DeleteInsuranceCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Insurance company deleted successfully.']);
    }

    public function restore(string $uuid, RestoreInsuranceCompanyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Insurance company restored successfully.']);
    }

    public function bulkDelete(BulkDeleteInsuranceCompanyRequest $request, BulkDeleteInsuranceCompanyHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteInsuranceCompanyData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} insurance company record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\MortgageCompanies\Application\Commands\CreateMortgageCompany\CreateMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Commands\DeleteMortgageCompany\DeleteMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Commands\RestoreMortgageCompany\RestoreMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Commands\UpdateMortgageCompany\UpdateMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\DTOs\CreateMortgageCompanyDTO;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterDTO;
use Modules\MortgageCompanies\Application\DTOs\UpdateMortgageCompanyDTO;
use Modules\MortgageCompanies\Application\Queries\GetMortgageCompany\GetMortgageCompanyHandler;
use Modules\MortgageCompanies\Application\Queries\ListMortgageCompanies\ListMortgageCompaniesHandler;
use Modules\MortgageCompanies\Infrastructure\Http\Requests\CreateMortgageCompanyRequest;
use Modules\MortgageCompanies\Infrastructure\Http\Requests\UpdateMortgageCompanyRequest;

final class MortgageCompanyController extends Controller
{
    public function index(
        ListMortgageCompaniesHandler $handler
    ): JsonResponse {
        $filters = MortgageCompanyFilterDTO::from(request()->all());
        $result = $handler->handle($filters);

        return response()->json($result);
    }

    public function show(
        string $uuid,
        GetMortgageCompanyHandler $handler
    ): JsonResponse {
        $mortgageCompany = $handler->handle($uuid);

        return response()->json($mortgageCompany);
    }

    public function store(
        CreateMortgageCompanyRequest $request,
        CreateMortgageCompanyHandler $handler
    ): JsonResponse {
        $dto = CreateMortgageCompanyDTO::from([
            ...$request->validated(),
            'userId' => auth()->id(),
        ]);

        $uuid = $handler->handle($dto);

        return response()->json(['uuid' => $uuid], 201);
    }

    public function update(
        string $uuid,
        UpdateMortgageCompanyRequest $request,
        UpdateMortgageCompanyHandler $handler
    ): JsonResponse {
        $dto = UpdateMortgageCompanyDTO::from($request->validated());
        $handler->handle($uuid, $dto);

        return response()->json(['message' => 'Mortgage company updated successfully']);
    }

    public function destroy(
        string $uuid,
        DeleteMortgageCompanyHandler $handler
    ): JsonResponse {
        $handler->handle($uuid);

        return response()->json(['message' => 'Mortgage company deleted successfully']);
    }

    public function restore(
        string $uuid,
        RestoreMortgageCompanyHandler $handler
    ): JsonResponse {
        $handler->handle($uuid);

        return response()->json(['message' => 'Mortgage company restored successfully']);
    }
}

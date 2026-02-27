<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Infrastructure\Adapters\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Src\Contexts\CompanyData\Application\Commands\CreateCompanyData\CreateCompanyDataCommand;
use Src\Contexts\CompanyData\Application\Commands\DeleteCompanyData\DeleteCompanyDataCommand;
use Src\Contexts\CompanyData\Application\Commands\RestoreCompanyData\RestoreCompanyDataCommand;
use Src\Contexts\CompanyData\Application\Commands\UpdateCompanyData\UpdateCompanyDataCommand;
use Src\Contexts\CompanyData\Application\DTOs\CompanyDataFilterDTO;
use Src\Contexts\CompanyData\Application\DTOs\CreateCompanyDataDTO;
use Src\Contexts\CompanyData\Application\DTOs\UpdateCompanyDataDTO;
use Src\Contexts\CompanyData\Application\Queries\GetCompanyData\GetCompanyDataQuery;
use Src\Contexts\CompanyData\Application\Queries\ListCompanyData\ListCompanyDataQuery;
use Src\Contexts\CompanyData\Infrastructure\Adapters\Http\Requests\CreateCompanyDataRequest;
use Src\Contexts\CompanyData\Infrastructure\Adapters\Http\Requests\UpdateCompanyDataRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

final class CompanyDataController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = CompanyDataFilterDTO::from($request->all());
        $result = Bus::dispatchSync(new ListCompanyDataQuery($filters));

        return response()->json($result);
    }

    public function show(string $uuid): JsonResponse
    {
        $dto = Bus::dispatchSync(new GetCompanyDataQuery(id: $uuid));

        return response()->json(['data' => $dto]);
    }

    public function store(CreateCompanyDataRequest $request): JsonResponse
    {
        $dto = CreateCompanyDataDTO::from($request->validated());
        $uuid = Bus::dispatchSync(new CreateCompanyDataCommand($dto));

        return response()->json([
            'message' => 'Company data created successfully',
            'uuid' => $uuid,
        ], 201);
    }

    public function update(UpdateCompanyDataRequest $request, string $uuid): JsonResponse
    {
        $dto = UpdateCompanyDataDTO::from($request->validated());
        Bus::dispatchSync(new UpdateCompanyDataCommand($uuid, $dto));

        return response()->json([
            'message' => 'Company data updated successfully',
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        Bus::dispatchSync(new DeleteCompanyDataCommand($uuid));

        return response()->json([
            'message' => 'Company data deleted successfully',
        ]);
    }

    public function restore(string $uuid): JsonResponse
    {
        Bus::dispatchSync(new RestoreCompanyDataCommand($uuid));

        return response()->json([
            'message' => 'Company data restored successfully',
        ]);
    }
}

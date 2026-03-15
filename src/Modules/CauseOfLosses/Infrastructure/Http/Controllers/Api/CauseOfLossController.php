<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\CauseOfLosses\Application\Commands\BulkDeleteCauseOfLossHandler;
use Src\Modules\CauseOfLosses\Application\Commands\CreateCauseOfLossHandler;
use Src\Modules\CauseOfLosses\Application\Commands\DeleteCauseOfLossHandler;
use Src\Modules\CauseOfLosses\Application\Commands\RestoreCauseOfLossHandler;
use Src\Modules\CauseOfLosses\Application\Commands\UpdateCauseOfLossHandler;
use Src\Modules\CauseOfLosses\Application\DTOs\BulkDeleteCauseOfLossData;
use Src\Modules\CauseOfLosses\Application\DTOs\CauseOfLossFilterData;
use Src\Modules\CauseOfLosses\Application\DTOs\StoreCauseOfLossData;
use Src\Modules\CauseOfLosses\Application\DTOs\UpdateCauseOfLossData;
use Src\Modules\CauseOfLosses\Application\Queries\GetCauseOfLossHandler;
use Src\Modules\CauseOfLosses\Application\Queries\ListCauseOfLossesHandler;
use Src\Modules\CauseOfLosses\Infrastructure\Http\Requests\BulkDeleteCauseOfLossRequest;
use Src\Modules\CauseOfLosses\Infrastructure\Http\Requests\StoreCauseOfLossRequest;
use Src\Modules\CauseOfLosses\Infrastructure\Http\Requests\UpdateCauseOfLossRequest;

final class CauseOfLossController extends Controller
{
    public function index(ListCauseOfLossesHandler $handler): JsonResponse
    {
        $causeOfLosses = $handler->handle(CauseOfLossFilterData::from(request()->query()));

        return response()->json([
            'data' => $causeOfLosses->items(),
            'meta' => [
                'current_page' => $causeOfLosses->currentPage(),
                'last_page' => $causeOfLosses->lastPage(),
                'per_page' => $causeOfLosses->perPage(),
                'total' => $causeOfLosses->total(),
            ],
        ]);
    }

    public function show(string $uuid, GetCauseOfLossHandler $handler): JsonResponse
    {
        $causeOfLoss = $handler->handle($uuid);

        if ($causeOfLoss === null) {
            return response()->json(['message' => 'Cause of loss not found.'], 404);
        }

        return response()->json($causeOfLoss);
    }

    public function store(StoreCauseOfLossRequest $request, CreateCauseOfLossHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreCauseOfLossData::from($request->validated()));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Cause of loss created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateCauseOfLossRequest $request, UpdateCauseOfLossHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateCauseOfLossData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Cause of loss updated successfully.']);
    }

    public function destroy(string $uuid, DeleteCauseOfLossHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Cause of loss deleted successfully.']);
    }

    public function restore(string $uuid, RestoreCauseOfLossHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Cause of loss restored successfully.']);
    }

    public function bulkDelete(BulkDeleteCauseOfLossRequest $request, BulkDeleteCauseOfLossHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteCauseOfLossData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} cause of loss record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}

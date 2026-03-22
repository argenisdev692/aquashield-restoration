<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CallHistory\Application\Commands\BulkDeleteCallHistoryCommand;
use Modules\CallHistory\Application\Commands\DeleteCallHistoryCommand;
use Modules\CallHistory\Application\Commands\RestoreCallHistoryCommand;
use Modules\CallHistory\Application\Commands\SyncCallsFromRetellCommand;
use Modules\CallHistory\Application\Commands\UpdateCallHistoryCommand;
use Modules\CallHistory\Application\DTOs\UpdateCallHistoryData;
use Modules\CallHistory\Application\Queries\GetCallHistoryQuery;
use Modules\CallHistory\Application\Queries\ListCallHistoryQuery;
use Modules\CallHistory\Infrastructure\Http\Requests\CallHistoryRequest;

/**
 * CallHistoryController
 *
 * Controller for managing call history records from Retell AI integration.
 *
 * @OA\Tag(
 *     name="Call History",
 *     description="API endpoints for managing call history records"
 * )
 */
final readonly class CallHistoryController
{
    public function __construct(
        private ListCallHistoryQuery $listQuery,
        private GetCallHistoryQuery $getQuery,
        private UpdateCallHistoryCommand $updateCommand,
        private DeleteCallHistoryCommand $deleteCommand,
        private RestoreCallHistoryCommand $restoreCommand,
        private BulkDeleteCallHistoryCommand $bulkDeleteCommand,
        private SyncCallsFromRetellCommand $syncCommand,
    ) {
    }

    /**
     * Display the call history index page (Inertia).
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('call-history/CallHistoryIndexPage');
    }

    /**
     * Display a single call history record (Inertia).
     *
     * @OA\Get(
     *     path="/api/call-history/{uuid}",
     *     tags={"Call History"},
     *     summary="Get a single call history record",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Call history UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="call_id", type="string"),
     *                 @OA\Property(property="agent_name", type="string", nullable=true),
     *                 @OA\Property(property="from_number", type="string", nullable=true),
     *                 @OA\Property(property="to_number", type="string", nullable=true),
     *                 @OA\Property(property="direction", type="string", enum={"inbound", "outbound"}),
     *                 @OA\Property(property="call_status", type="string"),
     *                 @OA\Property(property="call_type", type="string"),
     *                 @OA\Property(property="start_timestamp", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="duration_ms", type="integer", nullable=true),
     *                 @OA\Property(property="transcript", type="string", nullable=true),
     *                 @OA\Property(property="recording_url", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param string $uuid
     * @return Response
     */
    public function show(string $uuid): Response
    {
        $call = $this->getQuery->execute($uuid);

        if ($call === null) {
            abort(404, 'Call history not found');
        }

        return Inertia::render('call-history/CallHistoryShowPage', [
            'call' => $call->toArray(),
        ]);
    }

    /**
     * List call history records (paginated).
     *
     * @OA\Get(
     *     path="/api/call-history/data/admin/list",
     *     tags={"Call History"},
     *     summary="List call history records",
     *     description="Get paginated list of call history records with filtering and sorting",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="direction", in="query", required=false, @OA\Schema(type="string", enum={"inbound", "outbound"})),
     *     @OA\Parameter(name="call_type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="sort_field", in="query", required=false, @OA\Schema(type="string", default="start_timestamp")),
     *     @OA\Parameter(name="sort_direction", in="query", required=false, @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param CallHistoryRequest $request
     * @return JsonResponse
     */
    public function data(CallHistoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->listQuery->execute(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            direction: $validated['direction'] ?? null,
            callType: $validated['call_type'] ?? null,
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
            sortField: $validated['sort_field'] ?? 'start_timestamp',
            sortDirection: $validated['sort_direction'] ?? 'desc',
            perPage: (int) ($validated['per_page'] ?? 10),
            page: (int) ($validated['page'] ?? 1),
        );

        return response()->json($result);
    }

    /**
     * Get a single call history record (JSON API).
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function get(string $uuid): JsonResponse
    {
        $call = $this->getQuery->execute($uuid);

        if ($call === null) {
            return response()->json(['error' => 'Call history not found'], 404);
        }

        return response()->json(['data' => $call->toArray()]);
    }

    /**
     * Update a call history record.
     *
     * @OA\Put(
     *     path="/api/call-history/data/admin/{uuid}",
     *     tags={"Call History"},
     *     summary="Update call history record",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="agent_name", type="string", nullable=true),
     *             @OA\Property(property="call_status", type="string"),
     *             @OA\Property(property="call_type", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param CallHistoryRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(CallHistoryRequest $request, string $uuid): JsonResponse
    {
        $validated = $request->validated();

        try {
            $data = new UpdateCallHistoryData(
                agentName: $validated['agent_name'] ?? null,
                callStatus: $validated['call_status'] ?? null,
                callType: $validated['call_type'] ?? null,
            );

            $call = $this->updateCommand->execute($uuid, $data);

            return response()->json([
                'message' => 'Call history updated successfully',
                'data' => $call,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update call history', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Soft delete a call history record.
     *
     * @OA\Delete(
     *     path="/api/call-history/data/admin/{uuid}",
     *     tags={"Call History"},
     *     summary="Delete call history record",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->deleteCommand->execute($uuid);

            return response()->json([
                'message' => 'Call history deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete call history', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore a soft-deleted call history record.
     *
     * @OA\Post(
     *     path="/api/call-history/data/admin/{uuid}/restore",
     *     tags={"Call History"},
     *     summary="Restore deleted call history record",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restored successfully",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function restore(string $uuid): JsonResponse
    {
        try {
            $this->restoreCommand->execute($uuid);

            return response()->json([
                'message' => 'Call history restored successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to restore call history', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete call history records.
     *
     * @OA\Post(
     *     path="/api/call-history/data/admin/bulk-delete",
     *     tags={"Call History"},
     *     summary="Bulk delete call history records",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="count", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=400, description="No UUIDs provided"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param CallHistoryRequest $request
     * @return JsonResponse
     */
    public function bulkDelete(CallHistoryRequest $request): JsonResponse
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            return response()->json(['error' => 'No UUIDs provided'], 400);
        }

        try {
            $count = $this->bulkDeleteCommand->execute($uuids);

            return response()->json([
                'message' => "{$count} call histories deleted successfully",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete call histories', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Synchronize calls from Retell AI API.
     *
     * @OA\Post(
     *     path="/api/call-history/data/admin/sync",
     *     tags={"Call History"},
     *     summary="Sync calls from Retell AI",
     *     description="Fetch and synchronize call records from Retell AI API",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="limit", type="integer", default=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Synchronized successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="created", type="integer"),
     *                 @OA\Property(property="updated", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Sync failed"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     *
     * @param CallHistoryRequest $request
     * @return JsonResponse
     */
    public function sync(CallHistoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $filters = [];

        if (isset($validated['start_date']) && isset($validated['end_date'])) {
            $startTimestamp = \Carbon\Carbon::parse($validated['start_date'])->startOfDay()->timestamp * 1000;
            $endTimestamp = \Carbon\Carbon::parse($validated['end_date'])->endOfDay()->timestamp * 1000;

            $filters['time_range'] = [
                'start_timestamp' => $startTimestamp,
                'end_timestamp' => $endTimestamp,
            ];
        }

        if (isset($validated['limit'])) {
            $filters['limit'] = $validated['limit'];
        }

        try {
            $result = $this->syncCommand->execute($filters);

            return response()->json([
                'message' => 'Calls synchronized successfully',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync calls from Retell AI', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\Appointments\Application\Commands\BulkDeleteAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\CreateAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\DeleteAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\RestoreAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\UpdateAppointmentHandler;
use Src\Modules\Appointments\Application\DTOs\AppointmentFilterData;
use Src\Modules\Appointments\Application\DTOs\BulkDeleteAppointmentData;
use Src\Modules\Appointments\Application\DTOs\StoreAppointmentData;
use Src\Modules\Appointments\Application\DTOs\UpdateAppointmentData;
use Src\Modules\Appointments\Application\Queries\GetAppointmentHandler;
use Src\Modules\Appointments\Application\Queries\ListAppointmentsHandler;
use Src\Modules\Appointments\Infrastructure\Http\Requests\BulkDeleteAppointmentRequest;
use Src\Modules\Appointments\Infrastructure\Http\Requests\StoreAppointmentRequest;
use Src\Modules\Appointments\Infrastructure\Http\Requests\UpdateAppointmentRequest;

final class AppointmentController extends Controller
{
    public function index(ListAppointmentsHandler $handler): JsonResponse
    {
        $appointments = $handler->handle(AppointmentFilterData::from(request()->query()));

        return response()->json([
            'data' => $appointments->items(),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    public function show(string $uuid, GetAppointmentHandler $handler): JsonResponse
    {
        $appointment = $handler->handle($uuid);

        if ($appointment === null) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        return response()->json($appointment);
    }

    public function store(StoreAppointmentRequest $request, CreateAppointmentHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreAppointmentData::from($request->validated()));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Appointment created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateAppointmentRequest $request, UpdateAppointmentHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateAppointmentData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Appointment updated successfully.']);
    }

    public function destroy(string $uuid, DeleteAppointmentHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Appointment deleted successfully.']);
    }

    public function restore(string $uuid, RestoreAppointmentHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Appointment restored successfully.']);
    }

    public function bulkDelete(BulkDeleteAppointmentRequest $request, BulkDeleteAppointmentHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteAppointmentData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} appointment record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}

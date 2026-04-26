<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;
use Shared\Infrastructure\Export\SimpleTableExportResponder;
use Illuminate\Http\Request;
use Src\Modules\Appointments\Application\Commands\BulkDeleteAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\CreateAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\DeleteAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\RescheduleAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\RestoreAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\UpdateAppointmentHandler;
use Src\Modules\Appointments\Application\Commands\UpdateAppointmentStatusHandler;
use Src\Modules\Appointments\Application\DTOs\AppointmentFilterData;
use Src\Modules\Appointments\Application\DTOs\BulkDeleteAppointmentData;
use Src\Modules\Appointments\Application\DTOs\RescheduleAppointmentData;
use Src\Modules\Appointments\Application\DTOs\StoreAppointmentData;
use Src\Modules\Appointments\Application\DTOs\UpdateAppointmentData;
use Src\Modules\Appointments\Application\DTOs\UpdateAppointmentStatusData;
use Src\Modules\Appointments\Application\Queries\GetAppointmentHandler;
use Src\Modules\Appointments\Application\Queries\ListAppointmentsHandler;
use Src\Modules\Appointments\Application\Queries\ListCalendarEventsHandler;
use Src\Modules\Appointments\Infrastructure\Http\Requests\BulkDeleteAppointmentRequest;
use Src\Modules\Appointments\Infrastructure\Http\Requests\ExportAppointmentRequest;
use Src\Modules\Appointments\Infrastructure\Http\Requests\RescheduleAppointmentRequest;
use Src\Modules\Appointments\Infrastructure\Http\Requests\StoreAppointmentRequest;
use Src\Modules\Appointments\Infrastructure\Http\Requests\UpdateAppointmentRequest;
use Src\Modules\Appointments\Infrastructure\Http\Requests\UpdateAppointmentStatusRequest;
use Src\Modules\Appointments\Infrastructure\Persistence\Eloquent\Models\AppointmentEloquentModel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function export(ExportAppointmentRequest $request, SimpleTableExportResponder $exportResponder): Response|BinaryFileResponse
    {
        $validated = $request->validated();
        $filters = AppointmentFilterData::from($validated);
        $rows = $this->buildExportQuery($filters)->get()->map(
            static fn (AppointmentEloquentModel $appointment): array => [
                trim($appointment->first_name . ' ' . $appointment->last_name),
                $appointment->phone,
                $appointment->email,
                $appointment->inspection_status,
                $appointment->status_lead,
                $appointment->inspection_date?->format('Y-m-d') ?? '-',
                $appointment->created_at?->format('Y-m-d H:i:s') ?? '-',
                $appointment->deleted_at?->format('Y-m-d H:i:s') ?? '-',
            ],
        )->all();

        return $exportResponder->download(
            $request,
            'Appointments Report',
            'appointments-' . now()->format('Y-m-d'),
            ['Full Name', 'Phone', 'Email', 'Inspection Status', 'Lead Status', 'Inspection Date', 'Created At', 'Deleted At'],
            $rows,
            'exports.pdf.appointments',
        );
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

    public function calendarEvents(Request $request, ListCalendarEventsHandler $handler): JsonResponse
    {
        $events = $handler->handle(
            start: $request->query('start') !== null ? (string) $request->query('start') : null,
            end: $request->query('end') !== null ? (string) $request->query('end') : null,
        );

        return response()->json($events);
    }

    public function reschedule(string $uuid, RescheduleAppointmentRequest $request, RescheduleAppointmentHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, RescheduleAppointmentData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Appointment rescheduled successfully.']);
    }

    public function updateStatus(string $uuid, UpdateAppointmentStatusRequest $request, UpdateAppointmentStatusHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateAppointmentStatusData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Appointment status updated successfully.']);
    }

    private function buildExportQuery(AppointmentFilterData $filters): Builder
    {
        return AppointmentEloquentModel::query()
            ->withTrashed()
            ->select([
                'first_name',
                'last_name',
                'phone',
                'email',
                'inspection_status',
                'status_lead',
                'inspection_date',
                'created_at',
                'deleted_at',
            ])
            ->when($filters->search, static function (Builder $builder, string $search): void {
                $builder->where(static function (Builder $nested) use ($search): void {
                    $nested->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                });
            })
            ->when($filters->inspectionStatus, static fn (Builder $builder, string $inspectionStatus): Builder => $builder->where('inspection_status', $inspectionStatus))
            ->when($filters->statusLead, static fn (Builder $builder, string $statusLead): Builder => $builder->where('status_lead', $statusLead))
            ->when($filters->status === 'active', static fn (Builder $builder): Builder => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn (Builder $builder): Builder => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn (Builder $builder, string $dateFrom): Builder => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn (Builder $builder, string $dateTo): Builder => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at');
    }
}

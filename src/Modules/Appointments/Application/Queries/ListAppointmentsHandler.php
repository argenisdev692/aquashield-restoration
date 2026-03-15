<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Appointments\Application\DTOs\AppointmentFilterData;
use Src\Modules\Appointments\Application\Queries\ReadModels\AppointmentListReadModel;
use Src\Modules\Appointments\Infrastructure\Persistence\Eloquent\Models\AppointmentEloquentModel;

final class ListAppointmentsHandler
{
    public function handle(AppointmentFilterData $filters): LengthAwarePaginator
    {
        $query = AppointmentEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
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
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                });
            })
            ->when($filters->inspectionStatus, static fn ($builder, string $inspectionStatus) => $builder->where('inspection_status', $inspectionStatus))
            ->when($filters->statusLead, static fn ($builder, string $statusLead) => $builder->where('status_lead', $statusLead))
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->dateFrom, static fn ($builder, string $dateFrom) => $builder->whereDate('created_at', '>=', $dateFrom))
            ->when($filters->dateTo, static fn ($builder, string $dateTo) => $builder->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (AppointmentEloquentModel $appointment): AppointmentListReadModel => new AppointmentListReadModel(
                uuid: $appointment->uuid,
                fullName: trim($appointment->first_name . ' ' . $appointment->last_name),
                phone: $appointment->phone,
                email: $appointment->email,
                inspectionStatus: $appointment->inspection_status,
                statusLead: $appointment->status_lead,
                inspectionDate: $appointment->inspection_date?->toDateString(),
                createdAt: $appointment->created_at?->toIso8601String() ?? '',
                deletedAt: $appointment->deleted_at?->toIso8601String(),
            ));
    }
}

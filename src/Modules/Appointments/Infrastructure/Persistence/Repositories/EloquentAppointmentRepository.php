<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Persistence\Repositories;

use Src\Modules\Appointments\Domain\Entities\Appointment;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;
use Src\Modules\Appointments\Infrastructure\Persistence\Eloquent\Models\AppointmentEloquentModel;
use Src\Modules\Appointments\Infrastructure\Persistence\Mappers\AppointmentMapper;

final class EloquentAppointmentRepository implements AppointmentRepositoryPort
{
    public function __construct(
        private readonly AppointmentMapper $mapper,
    ) {}

    public function find(AppointmentId $id): ?Appointment
    {
        $model = AppointmentEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(Appointment $appointment): void
    {
        $this->mapper->toEloquent($appointment)->save();
    }

    public function softDelete(AppointmentId $id): void
    {
        AppointmentEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(AppointmentId $id): void
    {
        AppointmentEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (AppointmentId $id): string => $id->toString(),
            $ids,
        );

        return AppointmentEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Domain\Ports;

use Src\Modules\Appointments\Domain\Entities\Appointment;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

interface AppointmentRepositoryPort
{
    public function find(AppointmentId $id): ?Appointment;

    public function save(Appointment $appointment): void;

    public function softDelete(AppointmentId $id): void;

    public function restore(AppointmentId $id): void;

    public function bulkSoftDelete(array $ids): int;
}

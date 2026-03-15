<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use Src\Modules\Appointments\Application\DTOs\BulkDeleteAppointmentData;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class BulkDeleteAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteAppointmentData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): AppointmentId => AppointmentId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkSoftDelete($ids);
    }
}

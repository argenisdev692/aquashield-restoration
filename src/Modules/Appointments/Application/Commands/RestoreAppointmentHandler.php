<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class RestoreAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->restore(AppointmentId::fromString($uuid));
    }
}

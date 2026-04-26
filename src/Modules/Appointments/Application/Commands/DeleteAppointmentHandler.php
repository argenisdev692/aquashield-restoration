<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use Src\Modules\Appointments\Domain\Ports\AppointmentMailerPort;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class DeleteAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
        private readonly AppointmentMailerPort $mailer,
    ) {}

    public function handle(string $uuid): void
    {
        $id = AppointmentId::fromString($uuid);
        $appointment = $this->repository->find($id);

        if ($appointment !== null) {
            $this->mailer->sendCancelled($appointment, 'cancelled');
        }

        $this->repository->softDelete($id);
    }
}

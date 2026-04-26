<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Domain\Ports;

use Src\Modules\Appointments\Domain\Entities\Appointment;

interface AppointmentMailerPort
{
    public function sendConfirmed(Appointment $appointment): void;

    public function sendRescheduled(Appointment $appointment, ?string $previousDate, ?string $previousTime): void;

    public function sendCancelled(Appointment $appointment, string $reason = 'cancelled'): void;
}

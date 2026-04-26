<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use RuntimeException;
use Src\Modules\Appointments\Application\DTOs\UpdateAppointmentData;
use Src\Modules\Appointments\Domain\Entities\Appointment;
use Src\Modules\Appointments\Domain\Ports\AppointmentMailerPort;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class UpdateAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
        private readonly AppointmentMailerPort $mailer,
    ) {}

    public function handle(string $uuid, UpdateAppointmentData $data): void
    {
        $appointment = $this->repository->find(AppointmentId::fromString($uuid));

        if ($appointment === null) {
            throw new RuntimeException('Appointment not found.');
        }

        $previousStatus = $appointment->inspectionStatus();
        $previousDate = $appointment->inspectionDate();
        $previousTime = $appointment->inspectionTime();

        $appointment->update(
            firstName: $data->firstName,
            lastName: $data->lastName,
            phone: $data->phone,
            email: $data->email,
            address: $data->address,
            address2: $data->address2,
            city: $data->city,
            state: $data->state,
            zipcode: $data->zipcode,
            country: $data->country,
            insuranceProperty: $data->insuranceProperty,
            message: $data->message,
            smsConsent: $data->smsConsent,
            registrationDate: $data->registrationDate,
            inspectionDate: $data->inspectionDate,
            inspectionTime: $data->inspectionTime,
            notes: $data->notes,
            owner: $data->owner,
            damageDetail: $data->damageDetail,
            intentToClaim: $data->intentToClaim,
            leadSource: $data->leadSource,
            followUpDate: $data->followUpDate,
            additionalNote: $data->additionalNote,
            inspectionStatus: $data->inspectionStatus,
            statusLead: $data->statusLead,
            latitude: $data->latitude,
            longitude: $data->longitude,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($appointment);

        $this->dispatchEmail(
            appointment: $appointment,
            previousStatus: $previousStatus,
            previousDate: $previousDate,
            previousTime: $previousTime,
        );
    }

    private function dispatchEmail(
        Appointment $appointment,
        string $previousStatus,
        ?string $previousDate,
        ?string $previousTime,
    ): void {
        $newStatus = $appointment->inspectionStatus();
        $dateChanged = $appointment->inspectionDate() !== $previousDate;
        $timeChanged = $appointment->inspectionTime() !== $previousTime;

        if ($newStatus === 'Declined' && $previousStatus !== 'Declined') {
            $this->mailer->sendCancelled($appointment, 'declined');

            return;
        }

        if ($newStatus === 'Confirmed' && $previousStatus !== 'Confirmed') {
            $this->mailer->sendConfirmed($appointment);

            return;
        }

        if ($newStatus === 'Confirmed' && ($dateChanged || $timeChanged)) {
            $this->mailer->sendRescheduled($appointment, $previousDate, $previousTime);
        }
    }
}

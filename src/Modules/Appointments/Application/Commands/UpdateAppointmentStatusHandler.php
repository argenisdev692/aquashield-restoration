<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use RuntimeException;
use Src\Modules\Appointments\Application\DTOs\UpdateAppointmentStatusData;
use Src\Modules\Appointments\Domain\Ports\AppointmentMailerPort;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class UpdateAppointmentStatusHandler
{
    private const ALLOWED_STATUSES = ['Pending', 'Confirmed', 'Declined', 'Completed'];

    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
        private readonly AppointmentMailerPort $mailer,
    ) {}

    public function handle(string $uuid, UpdateAppointmentStatusData $data): void
    {
        if (!in_array($data->inspectionStatus, self::ALLOWED_STATUSES, true)) {
            throw new RuntimeException('Invalid inspection status.');
        }

        $appointment = $this->repository->find(AppointmentId::fromString($uuid));

        if ($appointment === null) {
            throw new RuntimeException('Appointment not found.');
        }

        $previousStatus = $appointment->inspectionStatus();

        if ($previousStatus === $data->inspectionStatus) {
            return;
        }

        $statusLead = match ($data->inspectionStatus) {
            'Confirmed', 'Completed' => 'Called',
            'Declined' => 'Declined',
            default => $appointment->statusLead(),
        };

        $appointment->update(
            firstName: $appointment->firstName(),
            lastName: $appointment->lastName(),
            phone: $appointment->phone(),
            email: $appointment->email(),
            address: $appointment->address(),
            address2: $appointment->address2(),
            city: $appointment->city(),
            state: $appointment->state(),
            zipcode: $appointment->zipcode(),
            country: $appointment->country(),
            insuranceProperty: $appointment->insuranceProperty(),
            message: $appointment->message(),
            smsConsent: $appointment->smsConsent(),
            registrationDate: $appointment->registrationDate(),
            inspectionDate: $appointment->inspectionDate(),
            inspectionTime: $appointment->inspectionTime(),
            notes: $appointment->notes(),
            owner: $appointment->owner(),
            damageDetail: $appointment->damageDetail(),
            intentToClaim: $appointment->intentToClaim(),
            leadSource: $appointment->leadSource(),
            followUpDate: $appointment->followUpDate(),
            additionalNote: $appointment->additionalNote(),
            inspectionStatus: $data->inspectionStatus,
            statusLead: $statusLead,
            latitude: $appointment->latitude(),
            longitude: $appointment->longitude(),
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($appointment);

        match ($data->inspectionStatus) {
            'Confirmed' => $this->mailer->sendConfirmed($appointment),
            'Declined' => $this->mailer->sendCancelled($appointment, 'declined'),
            default => null,
        };
    }
}

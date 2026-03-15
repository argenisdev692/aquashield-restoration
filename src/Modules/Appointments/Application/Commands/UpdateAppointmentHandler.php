<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use RuntimeException;
use Src\Modules\Appointments\Application\DTOs\UpdateAppointmentData;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class UpdateAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
    ) {}

    public function handle(string $uuid, UpdateAppointmentData $data): void
    {
        $appointment = $this->repository->find(AppointmentId::fromString($uuid));

        if ($appointment === null) {
            throw new RuntimeException('Appointment not found.');
        }

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
    }
}

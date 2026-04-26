<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use Src\Modules\Appointments\Application\DTOs\StoreAppointmentData;
use Src\Modules\Appointments\Domain\Entities\Appointment;
use Src\Modules\Appointments\Domain\Ports\AppointmentMailerPort;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class CreateAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
        private readonly AppointmentMailerPort $mailer,
    ) {}

    public function handle(StoreAppointmentData $data): string
    {
        $id = AppointmentId::generate();
        $appointment = Appointment::create(
            id: $id,
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
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($appointment);

        if ($appointment->inspectionStatus() === 'Confirmed') {
            $this->mailer->sendConfirmed($appointment);
        }

        return $id->toString();
    }
}

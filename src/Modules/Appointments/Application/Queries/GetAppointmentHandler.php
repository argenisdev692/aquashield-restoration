<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Queries;

use Src\Modules\Appointments\Application\Queries\ReadModels\AppointmentReadModel;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;

final class GetAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): ?AppointmentReadModel
    {
        $appointment = $this->repository->find(AppointmentId::fromString($uuid));

        if ($appointment === null) {
            return null;
        }

        return new AppointmentReadModel(
            uuid: $appointment->id()->toString(),
            fullName: $appointment->fullName(),
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
            inspectionStatus: $appointment->inspectionStatus(),
            statusLead: $appointment->statusLead(),
            latitude: $appointment->latitude(),
            longitude: $appointment->longitude(),
            createdAt: $appointment->createdAt(),
            updatedAt: $appointment->updatedAt(),
            deletedAt: $appointment->deletedAt(),
        );
    }
}

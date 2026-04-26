<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Application\Commands;

use RuntimeException;
use Src\Modules\Appointments\Application\DTOs\RescheduleAppointmentData;
use Src\Modules\Appointments\Domain\Ports\AppointmentMailerPort;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Domain\ValueObjects\AppointmentId;
use Src\Modules\Appointments\Infrastructure\Persistence\Eloquent\Models\AppointmentEloquentModel;

final class RescheduleAppointmentHandler
{
    public function __construct(
        private readonly AppointmentRepositoryPort $repository,
        private readonly AppointmentMailerPort $mailer,
    ) {}

    public function handle(string $uuid, RescheduleAppointmentData $data): void
    {
        $appointment = $this->repository->find(AppointmentId::fromString($uuid));

        if ($appointment === null) {
            throw new RuntimeException('Appointment not found.');
        }

        $previousDate = $appointment->inspectionDate();
        $previousTime = $appointment->inspectionTime();

        $hasConflict = AppointmentEloquentModel::query()
            ->where('uuid', '!=', $appointment->id()->toString())
            ->whereDate('inspection_date', $data->inspectionDate)
            ->whereTime('inspection_time', $data->inspectionTime)
            ->whereNotIn('inspection_status', ['Declined'])
            ->exists();

        if ($hasConflict) {
            throw new RuntimeException('Schedule conflict: another appointment is already booked for this date and time.');
        }

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
            inspectionDate: $data->inspectionDate,
            inspectionTime: $data->inspectionTime,
            notes: $appointment->notes(),
            owner: $appointment->owner(),
            damageDetail: $appointment->damageDetail(),
            intentToClaim: $appointment->intentToClaim(),
            leadSource: $appointment->leadSource(),
            followUpDate: $appointment->followUpDate(),
            additionalNote: $appointment->additionalNote(),
            inspectionStatus: 'Confirmed',
            statusLead: 'Called',
            latitude: $appointment->latitude(),
            longitude: $appointment->longitude(),
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($appointment);

        $this->mailer->sendRescheduled($appointment, $previousDate, $previousTime);
    }
}

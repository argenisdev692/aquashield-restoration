<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\CompanyData\Infrastructure\Utils\CompanyDataHelper;
use Src\Modules\Appointments\Domain\Entities\Appointment;
use Src\Modules\Appointments\Domain\Ports\AppointmentMailerPort;
use Src\Modules\Appointments\Infrastructure\Notifications\AppointmentCancelledNotification;
use Src\Modules\Appointments\Infrastructure\Notifications\AppointmentConfirmedNotification;
use Src\Modules\Appointments\Infrastructure\Notifications\AppointmentRescheduledNotification;
use Throwable;

final class AppointmentMailer implements AppointmentMailerPort
{
    public function sendConfirmed(Appointment $appointment): void
    {
        $email = $appointment->email();

        if ($email === null) {
            return;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new AppointmentConfirmedNotification(
                    appointment: $this->toArray($appointment),
                    company: CompanyDataHelper::getCompanyInfo(),
                ));
        } catch (Throwable $exception) {
            Log::error('Failed to dispatch appointment confirmed email', [
                'uuid' => $appointment->id()->toString(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function sendRescheduled(Appointment $appointment, ?string $previousDate, ?string $previousTime): void
    {
        $email = $appointment->email();

        if ($email === null) {
            return;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new AppointmentRescheduledNotification(
                    appointment: $this->toArray($appointment),
                    company: CompanyDataHelper::getCompanyInfo(),
                    previousDate: $previousDate,
                    previousTime: $previousTime,
                ));
        } catch (Throwable $exception) {
            Log::error('Failed to dispatch appointment rescheduled email', [
                'uuid' => $appointment->id()->toString(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function sendCancelled(Appointment $appointment, string $reason = 'cancelled'): void
    {
        $email = $appointment->email();

        if ($email === null) {
            return;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new AppointmentCancelledNotification(
                    appointment: $this->toArray($appointment),
                    company: CompanyDataHelper::getCompanyInfo(),
                    reason: $reason,
                ));
        } catch (Throwable $exception) {
            Log::error('Failed to dispatch appointment cancelled email', [
                'uuid' => $appointment->id()->toString(),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(Appointment $appointment): array
    {
        return [
            'uuid' => $appointment->id()->toString(),
            'first_name' => $appointment->firstName(),
            'last_name' => $appointment->lastName(),
            'full_name' => $appointment->fullName(),
            'phone' => $appointment->phone(),
            'email' => $appointment->email(),
            'address' => $appointment->address(),
            'address_2' => $appointment->address2(),
            'city' => $appointment->city(),
            'state' => $appointment->state(),
            'zipcode' => $appointment->zipcode(),
            'country' => $appointment->country(),
            'inspection_date' => $appointment->inspectionDate(),
            'inspection_time' => $appointment->inspectionTime(),
            'inspection_status' => $appointment->inspectionStatus(),
            'status_lead' => $appointment->statusLead(),
            'notes' => $appointment->notes(),
            'damage_detail' => $appointment->damageDetail(),
            'message' => $appointment->message(),
            'insurance_property' => $appointment->insuranceProperty(),
        ];
    }
}

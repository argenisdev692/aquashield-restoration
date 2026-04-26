<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AppointmentRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $appointment
     * @param  array<string, mixed>  $company
     */
    public function __construct(
        private readonly array $appointment,
        private readonly array $company,
        private readonly ?string $previousDate,
        private readonly ?string $previousTime,
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Appointment Updated - ' . ($this->company['name'] ?? config('app.name')))
            ->view('emails.appointments.rescheduled', [
                'appointment' => $this->appointment,
                'company' => $this->company,
                'previousDate' => $this->previousDate,
                'previousTime' => $this->previousTime,
            ]);
    }
}

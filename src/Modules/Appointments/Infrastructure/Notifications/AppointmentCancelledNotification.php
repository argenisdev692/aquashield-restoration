<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $appointment
     * @param  array<string, mixed>  $company
     */
    public function __construct(
        private readonly array $appointment,
        private readonly array $company,
        private readonly string $reason = 'cancelled',
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->reason === 'declined'
            ? 'Appointment Declined - ' . ($this->company['name'] ?? config('app.name'))
            : 'Appointment Cancelled - ' . ($this->company['name'] ?? config('app.name'));

        return (new MailMessage())
            ->subject($subject)
            ->view('emails.appointments.cancelled', [
                'appointment' => $this->appointment,
                'company' => $this->company,
                'reason' => $this->reason,
            ]);
    }
}

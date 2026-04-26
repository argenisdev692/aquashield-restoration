<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Src\Modules\Appointments\Domain\Ports\AppointmentMailerPort;
use Src\Modules\Appointments\Domain\Ports\AppointmentRepositoryPort;
use Src\Modules\Appointments\Infrastructure\Mail\AppointmentMailer;
use Src\Modules\Appointments\Infrastructure\Persistence\Repositories\EloquentAppointmentRepository;

final class AppointmentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AppointmentRepositoryPort::class,
            EloquentAppointmentRepository::class,
        );

        $this->app->bind(
            AppointmentMailerPort::class,
            AppointmentMailer::class,
        );
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])
            ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
    }
}

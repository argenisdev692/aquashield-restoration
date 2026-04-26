<?php

declare(strict_types=1);

namespace Src\Modules\Appointments\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\Appointments\Application\Queries\GetAppointmentHandler;

final class AppointmentPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('appointments/AppointmentsIndexPage');
    }

    public function calendar(): Response
    {
        return Inertia::render('appointments/AppointmentCalendarPage');
    }

    public function create(): Response
    {
        return Inertia::render('appointments/AppointmentCreatePage');
    }

    public function show(string $uuid, GetAppointmentHandler $handler): Response
    {
        $appointment = $handler->handle($uuid);

        if ($appointment === null) {
            abort(404);
        }

        return Inertia::render('appointments/AppointmentShowPage', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(string $uuid, GetAppointmentHandler $handler): Response
    {
        $appointment = $handler->handle($uuid);

        if ($appointment === null) {
            abort(404);
        }

        return Inertia::render('appointments/AppointmentEditPage', [
            'appointment' => $appointment,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\ServiceRequests\Application\Queries\GetServiceRequestHandler;

final class ServiceRequestPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('service-requests/ServiceRequestsIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('service-requests/ServiceRequestCreatePage');
    }

    public function show(string $uuid, GetServiceRequestHandler $handler): Response
    {
        $serviceRequest = $handler->handle($uuid);

        if ($serviceRequest === null) {
            abort(404);
        }

        return Inertia::render('service-requests/ServiceRequestShowPage', [
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function edit(string $uuid, GetServiceRequestHandler $handler): Response
    {
        $serviceRequest = $handler->handle($uuid);

        if ($serviceRequest === null) {
            abort(404);
        }

        return Inertia::render('service-requests/ServiceRequestEditPage', [
            'serviceRequest' => $serviceRequest,
        ]);
    }
}

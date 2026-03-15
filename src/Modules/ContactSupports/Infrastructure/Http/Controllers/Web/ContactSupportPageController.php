<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\ContactSupports\Application\Queries\GetContactSupportHandler;

final class ContactSupportPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('contact-supports/ContactSupportsIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('contact-supports/ContactSupportCreatePage');
    }

    public function show(string $uuid, GetContactSupportHandler $handler): Response
    {
        $contactSupport = $handler->handle($uuid);

        if ($contactSupport === null) {
            abort(404);
        }

        return Inertia::render('contact-supports/ContactSupportShowPage', [
            'contactSupport' => $contactSupport,
        ]);
    }

    public function edit(string $uuid, GetContactSupportHandler $handler): Response
    {
        $contactSupport = $handler->handle($uuid);

        if ($contactSupport === null) {
            abort(404);
        }

        return Inertia::render('contact-supports/ContactSupportEditPage', [
            'contactSupport' => $contactSupport,
        ]);
    }
}

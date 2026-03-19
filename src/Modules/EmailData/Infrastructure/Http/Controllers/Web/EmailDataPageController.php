<?php

declare(strict_types=1);

namespace Modules\EmailData\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\EmailData\Application\Queries\GetEmailDataHandler;

final class EmailDataPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('email-data/EmailDataIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('email-data/EmailDataCreatePage');
    }

    public function show(string $uuid, GetEmailDataHandler $handler): Response
    {
        $emailData = $handler->handle($uuid);

        if ($emailData === null) {
            abort(404);
        }

        return Inertia::render('email-data/EmailDataShowPage', [
            'emailData' => $emailData,
        ]);
    }

    public function edit(string $uuid, GetEmailDataHandler $handler): Response
    {
        $emailData = $handler->handle($uuid);

        if ($emailData === null) {
            abort(404);
        }

        return Inertia::render('email-data/EmailDataEditPage', [
            'emailData' => $emailData,
        ]);
    }
}

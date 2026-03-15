<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\CauseOfLosses\Application\Queries\GetCauseOfLossHandler;

final class CauseOfLossPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('cause-of-losses/CauseOfLossesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('cause-of-losses/CauseOfLossCreatePage');
    }

    public function show(string $uuid, GetCauseOfLossHandler $handler): Response
    {
        $causeOfLoss = $handler->handle($uuid);

        if ($causeOfLoss === null) {
            abort(404);
        }

        return Inertia::render('cause-of-losses/CauseOfLossShowPage', [
            'causeOfLoss' => $causeOfLoss,
        ]);
    }

    public function edit(string $uuid, GetCauseOfLossHandler $handler): Response
    {
        $causeOfLoss = $handler->handle($uuid);

        if ($causeOfLoss === null) {
            abort(404);
        }

        return Inertia::render('cause-of-losses/CauseOfLossEditPage', [
            'causeOfLoss' => $causeOfLoss,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\ClaimStatuses\Application\Queries\GetClaimStatusHandler;

final class ClaimStatusPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('claim-statuses/ClaimStatusesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('claim-statuses/ClaimStatusCreatePage');
    }

    public function show(string $uuid, GetClaimStatusHandler $handler): Response
    {
        $claimStatus = $handler->handle($uuid);

        if ($claimStatus === null) {
            abort(404);
        }

        return Inertia::render('claim-statuses/ClaimStatusShowPage', [
            'claimStatus' => $claimStatus,
        ]);
    }

    public function edit(string $uuid, GetClaimStatusHandler $handler): Response
    {
        $claimStatus = $handler->handle($uuid);

        if ($claimStatus === null) {
            abort(404);
        }

        return Inertia::render('claim-statuses/ClaimStatusEditPage', [
            'claimStatus' => $claimStatus,
        ]);
    }
}

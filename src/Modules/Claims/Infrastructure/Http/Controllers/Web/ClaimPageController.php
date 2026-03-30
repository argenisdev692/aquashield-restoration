<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\Claims\Application\Queries\GetClaimHandler;

final class ClaimPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('claims/ClaimsIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('claims/ClaimCreatePage');
    }

    public function show(string $uuid, GetClaimHandler $handler): Response
    {
        $claim = $handler->handle($uuid);

        if ($claim === null) {
            abort(404);
        }

        return Inertia::render('claims/ClaimShowPage', [
            'claim' => ['data' => $claim],
        ]);
    }

    public function edit(string $uuid, GetClaimHandler $handler): Response
    {
        $claim = $handler->handle($uuid);

        if ($claim === null) {
            abort(404);
        }

        return Inertia::render('claims/ClaimEditPage', [
            'claim' => ['data' => $claim],
        ]);
    }
}

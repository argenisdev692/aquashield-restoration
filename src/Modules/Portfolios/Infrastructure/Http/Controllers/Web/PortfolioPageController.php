<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\Portfolios\Application\Queries\GetPortfolioHandler;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class PortfolioPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('portfolios/PortfoliosIndexPage');
    }

    public function create(): Response
    {
        $projectTypes = ProjectTypeEloquentModel::query()
            ->whereNull('deleted_at')
            ->with('serviceCategory:id,uuid,category')
            ->orderBy('title')
            ->get(['id', 'uuid', 'title', 'service_category_id']);

        return Inertia::render('portfolios/PortfolioCreatePage', [
            'projectTypes' => $projectTypes->map(static fn ($pt) => [
                'uuid'                  => $pt->uuid,
                'title'                 => $pt->title,
                'service_category_name' => $pt->serviceCategory?->category,
            ]),
        ]);
    }

    public function show(string $uuid, GetPortfolioHandler $handler): Response
    {
        $portfolio = $handler->handle($uuid);

        if ($portfolio === null) {
            abort(404);
        }

        return Inertia::render('portfolios/PortfolioShowPage', [
            'portfolio' => $portfolio,
        ]);
    }

    public function edit(string $uuid, GetPortfolioHandler $handler): Response
    {
        $portfolio = $handler->handle($uuid);

        if ($portfolio === null) {
            abort(404);
        }

        $projectTypes = ProjectTypeEloquentModel::query()
            ->whereNull('deleted_at')
            ->with('serviceCategory:id,uuid,category')
            ->orderBy('title')
            ->get(['id', 'uuid', 'title', 'service_category_id']);

        return Inertia::render('portfolios/PortfolioEditPage', [
            'portfolio'    => $portfolio,
            'projectTypes' => $projectTypes->map(static fn ($pt) => [
                'uuid'                  => $pt->uuid,
                'title'                 => $pt->title,
                'service_category_name' => $pt->serviceCategory?->category,
            ]),
        ]);
    }
}

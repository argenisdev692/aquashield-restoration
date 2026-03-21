<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\ProjectTypes\Application\Queries\GetProjectTypeHandler;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ProjectTypePageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('project-types/ProjectTypesIndexPage');
    }

    public function create(): Response
    {
        $serviceCategories = ServiceCategoryEloquentModel::query()
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->get(['uuid', 'category', 'type']);

        return Inertia::render('project-types/ProjectTypeCreatePage', [
            'serviceCategories' => $serviceCategories,
        ]);
    }

    public function show(string $uuid, GetProjectTypeHandler $handler): Response
    {
        $projectType = $handler->handle($uuid);

        if ($projectType === null) {
            abort(404);
        }

        return Inertia::render('project-types/ProjectTypeShowPage', [
            'projectType' => $projectType,
        ]);
    }

    public function edit(string $uuid, GetProjectTypeHandler $handler): Response
    {
        $projectType = $handler->handle($uuid);

        if ($projectType === null) {
            abort(404);
        }

        $serviceCategories = ServiceCategoryEloquentModel::query()
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->get(['uuid', 'category', 'type']);

        return Inertia::render('project-types/ProjectTypeEditPage', [
            'projectType'       => $projectType,
            'serviceCategories' => $serviceCategories,
        ]);
    }
}

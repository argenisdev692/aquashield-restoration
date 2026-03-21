<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ProjectTypes\Application\DTOs\ProjectTypeFilterData;
use Src\Modules\ProjectTypes\Application\Queries\ReadModels\ProjectTypeListReadModel;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class ListProjectTypesHandler
{
    public function handle(ProjectTypeFilterData $filters): LengthAwarePaginator
    {
        $query = ProjectTypeEloquentModel::query()
            ->withTrashed()
            ->select([
                'project_types.uuid',
                'project_types.title',
                'project_types.description',
                'project_types.status',
                'project_types.created_at',
                'project_types.deleted_at',
                'service_categories.uuid as service_category_uuid',
                'service_categories.category as service_category_name',
            ])
            ->leftJoin('service_categories', 'service_categories.id', '=', 'project_types.service_category_id')
            ->when($filters->search, static function ($builder, string $search): void {
                $builder->where(static function ($nested) use ($search): void {
                    $nested->where('project_types.title', 'like', "%{$search}%")
                        ->orWhere('project_types.description', 'like', "%{$search}%");
                });
            })
            ->when($filters->status === 'active', static fn ($builder) => $builder->whereNull('project_types.deleted_at'))
            ->when($filters->status === 'deleted', static fn ($builder) => $builder->onlyTrashed())
            ->when($filters->serviceCategoryUuid, static fn ($builder, string $uuid) => $builder->where('service_categories.uuid', $uuid))
            ->when($filters->dateFrom, static fn ($builder, string $d) => $builder->whereDate('project_types.created_at', '>=', $d))
            ->when($filters->dateTo, static fn ($builder, string $d) => $builder->whereDate('project_types.created_at', '<=', $d))
            ->orderBy('project_types.title')
            ->orderByDesc('project_types.created_at');

        return $query
            ->paginate($filters->perPage, ['*'], 'page', $filters->page)
            ->through(static fn (ProjectTypeEloquentModel $m): ProjectTypeListReadModel => new ProjectTypeListReadModel(
                uuid: $m->uuid,
                title: $m->title,
                description: $m->description,
                status: $m->status,
                serviceCategoryUuid: $m->service_category_uuid ?? '',
                serviceCategoryName: $m->service_category_name,
                createdAt: $m->created_at?->toIso8601String() ?? '',
                deletedAt: $m->deleted_at?->toIso8601String(),
            ));
    }
}

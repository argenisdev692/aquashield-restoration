<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\Queries;

use Src\Modules\ProjectTypes\Application\Queries\ReadModels\ProjectTypeReadModel;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class GetProjectTypeHandler
{
    public function handle(string $uuid): ?ProjectTypeReadModel
    {
        $model = ProjectTypeEloquentModel::withTrashed()
            ->select([
                'project_types.uuid',
                'project_types.title',
                'project_types.description',
                'project_types.status',
                'project_types.created_at',
                'project_types.updated_at',
                'project_types.deleted_at',
                'service_categories.uuid as service_category_uuid',
                'service_categories.category as service_category_name',
            ])
            ->leftJoin('service_categories', 'service_categories.id', '=', 'project_types.service_category_id')
            ->where('project_types.uuid', $uuid)
            ->first();

        if ($model === null) {
            return null;
        }

        return new ProjectTypeReadModel(
            uuid: $model->uuid,
            title: $model->title,
            description: $model->description,
            status: $model->status,
            serviceCategoryUuid: $model->service_category_uuid ?? '',
            serviceCategoryName: $model->service_category_name,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}

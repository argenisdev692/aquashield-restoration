<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Persistence\Mappers;

use Src\Modules\ProjectTypes\Domain\Entities\ProjectType;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ProjectTypeMapper
{
    public function toDomain(ProjectTypeEloquentModel $model): ProjectType
    {
        $serviceCategoryUuid = ServiceCategoryEloquentModel::withTrashed()
            ->where('id', $model->service_category_id)
            ->value('uuid') ?? '';

        return ProjectType::reconstitute(
            id: ProjectTypeId::fromString($model->uuid),
            title: $model->title,
            description: $model->description,
            status: $model->status,
            serviceCategoryUuid: $serviceCategoryUuid,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(ProjectType $projectType): ProjectTypeEloquentModel
    {
        $model = ProjectTypeEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $projectType->id()->toString(),
        ]);

        $serviceCategory = ServiceCategoryEloquentModel::where('uuid', $projectType->serviceCategoryUuid())->first();

        $model->uuid                = $projectType->id()->toString();
        $model->title               = $projectType->title();
        $model->description         = $projectType->description();
        $model->status              = $projectType->status();
        $model->service_category_id = $serviceCategory?->id;

        if (!$model->exists) {
            $model->user_id = auth()->id();
        }

        return $model;
    }
}

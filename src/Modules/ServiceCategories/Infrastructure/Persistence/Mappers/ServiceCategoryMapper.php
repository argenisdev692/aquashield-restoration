<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Persistence\Mappers;

use Src\Modules\ServiceCategories\Domain\Entities\ServiceCategory;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ServiceCategoryMapper
{
    public function toDomain(ServiceCategoryEloquentModel $model): ServiceCategory
    {
        return ServiceCategory::reconstitute(
            id: ServiceCategoryId::fromString($model->uuid),
            category: $model->category,
            type: $model->type,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    public function toEloquent(ServiceCategory $serviceCategory): ServiceCategoryEloquentModel
    {
        $model = ServiceCategoryEloquentModel::withTrashed()->firstOrNew([
            'uuid' => $serviceCategory->id()->toString(),
        ]);

        $model->uuid     = $serviceCategory->id()->toString();
        $model->category = $serviceCategory->category();
        $model->type     = $serviceCategory->type();

        if (!$model->exists) {
            $model->user_id = auth()->id();
        }

        return $model;
    }
}

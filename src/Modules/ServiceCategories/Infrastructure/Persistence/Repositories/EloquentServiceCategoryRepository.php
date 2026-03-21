<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Persistence\Repositories;

use Src\Modules\ServiceCategories\Domain\Entities\ServiceCategory;
use Src\Modules\ServiceCategories\Domain\Ports\ServiceCategoryRepositoryPort;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Mappers\ServiceCategoryMapper;

final class EloquentServiceCategoryRepository implements ServiceCategoryRepositoryPort
{
    public function __construct(
        private readonly ServiceCategoryMapper $mapper,
    ) {}

    public function find(ServiceCategoryId $id): ?ServiceCategory
    {
        $model = ServiceCategoryEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(ServiceCategory $serviceCategory): void
    {
        $this->mapper->toEloquent($serviceCategory)->save();
    }

    public function softDelete(ServiceCategoryId $id): void
    {
        ServiceCategoryEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(ServiceCategoryId $id): void
    {
        ServiceCategoryEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (ServiceCategoryId $id): string => $id->toString(),
            $ids,
        );

        return ServiceCategoryEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}

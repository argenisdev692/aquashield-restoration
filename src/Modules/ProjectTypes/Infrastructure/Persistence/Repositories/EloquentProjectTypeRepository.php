<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Persistence\Repositories;

use Src\Modules\ProjectTypes\Domain\Entities\ProjectType;
use Src\Modules\ProjectTypes\Domain\Ports\ProjectTypeRepositoryPort;
use Src\Modules\ProjectTypes\Domain\ValueObjects\ProjectTypeId;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Mappers\ProjectTypeMapper;

final class EloquentProjectTypeRepository implements ProjectTypeRepositoryPort
{
    public function __construct(
        private readonly ProjectTypeMapper $mapper,
    ) {}

    public function find(ProjectTypeId $id): ?ProjectType
    {
        $model = ProjectTypeEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(ProjectType $projectType): void
    {
        $this->mapper->toEloquent($projectType)->save();
    }

    public function softDelete(ProjectTypeId $id): void
    {
        ProjectTypeEloquentModel::where('uuid', $id->toString())->delete();
    }

    public function restore(ProjectTypeId $id): void
    {
        ProjectTypeEloquentModel::withTrashed()
            ->where('uuid', $id->toString())
            ->restore();
    }

    public function bulkSoftDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (ProjectTypeId $id): string => $id->toString(),
            $ids,
        );

        return ProjectTypeEloquentModel::whereIn('uuid', $uuids)->delete();
    }
}

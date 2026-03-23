<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Persistence\Repositories;

use Src\Modules\FilesEsx\Domain\Entities\FileEsx;
use Src\Modules\FilesEsx\Domain\Ports\FileEsxRepositoryPort;
use Src\Modules\FilesEsx\Domain\ValueObjects\FileEsxId;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Mappers\FileEsxMapper;

final class EloquentFileEsxRepository implements FileEsxRepositoryPort
{
    public function __construct(
        private readonly FileEsxMapper $mapper,
    ) {}

    public function find(FileEsxId $id): ?FileEsx
    {
        $model = FileEsxEloquentModel::where('uuid', $id->toString())->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(FileEsx $fileEsx): void
    {
        $this->mapper->toEloquent($fileEsx)->save();
    }

    public function delete(FileEsxId $id): void
    {
        FileEsxEloquentModel::where('uuid', $id->toString())->forceDelete();
    }

    public function bulkDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (FileEsxId $id): string => $id->toString(),
            $ids,
        );

        return FileEsxEloquentModel::whereIn('uuid', $uuids)->forceDelete();
    }
}

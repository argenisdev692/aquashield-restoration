<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Repositories;

use Src\Modules\DocumentTemplateAdjusters\Domain\Entities\DocumentTemplateAdjuster;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\DocumentTemplateAdjusterRepositoryPort;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Mappers\DocumentTemplateAdjusterMapper;

final class EloquentDocumentTemplateAdjusterRepository implements DocumentTemplateAdjusterRepositoryPort
{
    public function __construct(
        private readonly DocumentTemplateAdjusterMapper $mapper,
    ) {}

    public function find(DocumentTemplateAdjusterId $id): ?DocumentTemplateAdjuster
    {
        $model = DocumentTemplateAdjusterEloquentModel::query()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(DocumentTemplateAdjuster $documentTemplateAdjuster): void
    {
        $this->mapper->toEloquent($documentTemplateAdjuster)->save();
    }

    public function delete(DocumentTemplateAdjusterId $id): void
    {
        DocumentTemplateAdjusterEloquentModel::query()
            ->where('uuid', $id->toString())
            ->delete();
    }

    public function bulkDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (DocumentTemplateAdjusterId $id): string => $id->toString(),
            $ids,
        );

        $existing = DocumentTemplateAdjusterEloquentModel::query()
            ->whereIn('uuid', $uuids)
            ->pluck('uuid')
            ->all();

        $firstMatch = array_find(
            $existing,
            static fn (string $uuid): bool => in_array($uuid, $uuids, true),
        );

        if ($firstMatch === null) {
            return 0;
        }

        return DocumentTemplateAdjusterEloquentModel::query()
            ->whereIn('uuid', $uuids)
            ->delete();
    }
}

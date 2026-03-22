<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Repositories;

use Src\Modules\DocumentTemplateAlliances\Domain\Entities\DocumentTemplateAlliance;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\DocumentTemplateAllianceRepositoryPort;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Mappers\DocumentTemplateAllianceMapper;

final class EloquentDocumentTemplateAllianceRepository implements DocumentTemplateAllianceRepositoryPort
{
    public function __construct(
        private readonly DocumentTemplateAllianceMapper $mapper,
    ) {}

    public function find(DocumentTemplateAllianceId $id): ?DocumentTemplateAlliance
    {
        $model = DocumentTemplateAllianceEloquentModel::query()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(DocumentTemplateAlliance $documentTemplateAlliance): void
    {
        $this->mapper->toEloquent($documentTemplateAlliance)->save();
    }

    public function delete(DocumentTemplateAllianceId $id): void
    {
        DocumentTemplateAllianceEloquentModel::query()
            ->where('uuid', $id->toString())
            ->delete();
    }

    public function bulkDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (DocumentTemplateAllianceId $id): string => $id->toString(),
            $ids,
        );

        return DocumentTemplateAllianceEloquentModel::query()
            ->whereIn('uuid', $uuids)
            ->delete();
    }
}

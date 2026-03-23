<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Persistence\Repositories;

use Src\Modules\DocumentTemplates\Domain\Entities\DocumentTemplate;
use Src\Modules\DocumentTemplates\Domain\Ports\DocumentTemplateRepositoryPort;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Mappers\DocumentTemplateMapper;

final class EloquentDocumentTemplateRepository implements DocumentTemplateRepositoryPort
{
    public function __construct(
        private readonly DocumentTemplateMapper $mapper,
    ) {}

    public function find(DocumentTemplateId $id): ?DocumentTemplate
    {
        $model = DocumentTemplateEloquentModel::query()
            ->where('uuid', $id->toString())
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function save(DocumentTemplate $documentTemplate): void
    {
        $this->mapper->toEloquent($documentTemplate)->save();
    }

    public function delete(DocumentTemplateId $id): void
    {
        DocumentTemplateEloquentModel::query()
            ->where('uuid', $id->toString())
            ->delete();
    }

    public function bulkDelete(array $ids): int
    {
        $uuids = array_map(
            static fn (DocumentTemplateId $id): string => $id->toString(),
            $ids,
        );

        return DocumentTemplateEloquentModel::query()
            ->whereIn('uuid', $uuids)
            ->delete();
    }
}

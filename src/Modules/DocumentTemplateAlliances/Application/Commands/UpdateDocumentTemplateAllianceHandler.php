<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\Commands;

use RuntimeException;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\UpdateDocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\DocumentTemplateAllianceRepositoryPort;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;

final class UpdateDocumentTemplateAllianceHandler
{
    public function __construct(
        private readonly DocumentTemplateAllianceRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    public function handle(string $uuid, UpdateDocumentTemplateAllianceData $data, mixed $file = null): void
    {
        $id     = DocumentTemplateAllianceId::fromString($uuid);
        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new RuntimeException("Document template alliance [{$uuid}] not found.");
        }

        $newPath = $entity->templatePathAlliance();

        if ($file !== null) {
            $this->storage->delete($entity->templatePathAlliance());
            $newPath = $this->storage->upload($file, 'document-templates');
        }

        $entity->update(
            templateNameAlliance: $data->templateNameAlliance,
            templateDescriptionAlliance: $data->templateDescriptionAlliance,
            templateTypeAlliance: $data->templateTypeAlliance,
            templatePathAlliance: $newPath,
            allianceCompanyId: $data->allianceCompanyId,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($entity);
    }
}

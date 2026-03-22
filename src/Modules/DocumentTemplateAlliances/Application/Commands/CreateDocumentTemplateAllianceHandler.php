<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\Commands;

use Src\Modules\DocumentTemplateAlliances\Application\DTOs\StoreDocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Domain\Entities\DocumentTemplateAlliance;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\DocumentTemplateAllianceRepositoryPort;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;

final class CreateDocumentTemplateAllianceHandler
{
    public function __construct(
        private readonly DocumentTemplateAllianceRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    #[\NoDiscard('UUID of the created document template alliance must be captured')]
    public function handle(StoreDocumentTemplateAllianceData $data, mixed $file): string
    {
        $id   = DocumentTemplateAllianceId::generate();
        $path = $this->storage->upload($file, 'document-templates');

        $entity = DocumentTemplateAlliance::create(
            id: $id,
            templateNameAlliance: $data->templateNameAlliance,
            templateDescriptionAlliance: $data->templateDescriptionAlliance,
            templateTypeAlliance: $data->templateTypeAlliance,
            templatePathAlliance: $path,
            allianceCompanyId: $data->allianceCompanyId,
            uploadedBy: $data->uploadedBy,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($entity);

        return $id->toString();
    }
}

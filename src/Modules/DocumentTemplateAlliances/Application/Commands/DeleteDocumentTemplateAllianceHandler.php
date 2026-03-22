<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\Commands;

use Src\Modules\DocumentTemplateAlliances\Domain\Ports\DocumentTemplateAllianceRepositoryPort;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;

final class DeleteDocumentTemplateAllianceHandler
{
    public function __construct(
        private readonly DocumentTemplateAllianceRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    public function handle(string $uuid): void
    {
        $id     = DocumentTemplateAllianceId::fromString($uuid);
        $entity = $this->repository->find($id);

        if ($entity !== null) {
            $this->storage->delete($entity->templatePathAlliance());
        }

        $this->repository->delete($id);
    }
}

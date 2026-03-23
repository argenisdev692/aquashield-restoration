<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\Commands;

use Src\Modules\DocumentTemplates\Domain\Ports\DocumentTemplateRepositoryPort;
use Src\Modules\DocumentTemplates\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;

final class DeleteDocumentTemplateHandler
{
    public function __construct(
        private readonly DocumentTemplateRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    public function handle(string $uuid): void
    {
        $id     = DocumentTemplateId::fromString($uuid);
        $entity = $this->repository->find($id);

        if ($entity !== null) {
            $this->storage->delete($entity->templatePath());
        }

        $this->repository->delete($id);
    }
}

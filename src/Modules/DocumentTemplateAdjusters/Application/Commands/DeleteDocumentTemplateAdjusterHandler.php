<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\Commands;

use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\DocumentTemplateAdjusterRepositoryPort;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;

final class DeleteDocumentTemplateAdjusterHandler
{
    public function __construct(
        private readonly DocumentTemplateAdjusterRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    public function handle(string $uuid): void
    {
        $id     = DocumentTemplateAdjusterId::fromString($uuid);
        $entity = $this->repository->find($id);

        if ($entity !== null) {
            $this->storage->delete($entity->templatePathAdjuster());
        }

        $this->repository->delete($id);
    }
}

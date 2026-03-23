<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\Commands;

use RuntimeException;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\UpdateDocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\DocumentTemplateAdjusterRepositoryPort;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;

final class UpdateDocumentTemplateAdjusterHandler
{
    public function __construct(
        private readonly DocumentTemplateAdjusterRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    public function handle(string $uuid, UpdateDocumentTemplateAdjusterData $data, mixed $file = null): void
    {
        $id     = DocumentTemplateAdjusterId::fromString($uuid);
        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new RuntimeException("Document template adjuster [{$uuid}] not found.");
        }

        $newPath = $entity->templatePathAdjuster();

        if ($file !== null) {
            $this->storage->delete($entity->templatePathAdjuster());
            $newPath = $this->storage->upload($file, 'document-template-adjusters');
        }

        $entity->update(
            templateDescriptionAdjuster: $data->templateDescriptionAdjuster,
            templateTypeAdjuster: $data->templateTypeAdjuster,
            templatePathAdjuster: $newPath,
            publicAdjusterId: $data->publicAdjusterId,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($entity);
    }
}

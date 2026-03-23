<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\Commands;

use RuntimeException;
use Src\Modules\DocumentTemplates\Application\DTOs\UpdateDocumentTemplateData;
use Src\Modules\DocumentTemplates\Domain\Ports\DocumentTemplateRepositoryPort;
use Src\Modules\DocumentTemplates\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;

final class UpdateDocumentTemplateHandler
{
    public function __construct(
        private readonly DocumentTemplateRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    public function handle(string $uuid, UpdateDocumentTemplateData $data, mixed $file): void
    {
        $id     = DocumentTemplateId::fromString($uuid);
        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new RuntimeException('Document template not found.');
        }

        $path = $entity->templatePath();

        if ($file !== null) {
            $this->storage->delete($entity->templatePath());
            $path = $this->storage->upload($file, 'document-templates');
        }

        $entity->update(
            templateName: $data->templateName,
            templateDescription: $data->templateDescription,
            templateType: $data->templateType,
            templatePath: $path,
            updatedAt: now()->toIso8601String(),
        );

        $this->repository->save($entity);
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\Commands;

use Src\Modules\DocumentTemplates\Application\DTOs\StoreDocumentTemplateData;
use Src\Modules\DocumentTemplates\Domain\Entities\DocumentTemplate;
use Src\Modules\DocumentTemplates\Domain\Ports\DocumentTemplateRepositoryPort;
use Src\Modules\DocumentTemplates\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;

final class CreateDocumentTemplateHandler
{
    public function __construct(
        private readonly DocumentTemplateRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    #[\NoDiscard('UUID of the created document template must be captured')]
    public function handle(StoreDocumentTemplateData $data, mixed $file): string
    {
        $id   = DocumentTemplateId::generate();
        $path = $this->storage->upload($file, 'document-templates');

        $entity = DocumentTemplate::create(
            id: $id,
            templateName: $data->templateName,
            templateDescription: $data->templateDescription,
            templateType: $data->templateType,
            templatePath: $path,
            uploadedBy: $data->uploadedBy,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($entity);

        return $id->toString();
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\Commands;

use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\StoreDocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Domain\Entities\DocumentTemplateAdjuster;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\DocumentTemplateAdjusterRepositoryPort;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\StoragePort;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;

final class CreateDocumentTemplateAdjusterHandler
{
    public function __construct(
        private readonly DocumentTemplateAdjusterRepositoryPort $repository,
        private readonly StoragePort $storage,
    ) {}

    #[\NoDiscard('UUID of the created document template adjuster must be captured')]
    public function handle(StoreDocumentTemplateAdjusterData $data, mixed $file): string
    {
        $id   = DocumentTemplateAdjusterId::generate();
        $path = $this->storage->upload($file, 'document-template-adjusters');

        $entity = DocumentTemplateAdjuster::create(
            id: $id,
            templateDescriptionAdjuster: $data->templateDescriptionAdjuster,
            templateTypeAdjuster: $data->templateTypeAdjuster,
            templatePathAdjuster: $path,
            publicAdjusterId: $data->publicAdjusterId,
            uploadedBy: $data->uploadedBy,
            createdAt: now()->toIso8601String(),
        );

        $this->repository->save($entity);

        return $id->toString();
    }
}

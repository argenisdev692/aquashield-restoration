<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\Commands;

use Src\Modules\DocumentTemplates\Application\DTOs\BulkDeleteDocumentTemplateData;
use Src\Modules\DocumentTemplates\Domain\Ports\DocumentTemplateRepositoryPort;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;

final class BulkDeleteDocumentTemplateHandler
{
    public function __construct(
        private readonly DocumentTemplateRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteDocumentTemplateData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): DocumentTemplateId => DocumentTemplateId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkDelete($ids);
    }
}

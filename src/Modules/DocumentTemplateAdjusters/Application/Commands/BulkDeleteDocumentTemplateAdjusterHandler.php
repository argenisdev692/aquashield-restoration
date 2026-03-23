<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Application\Commands;

use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\BulkDeleteDocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Domain\Ports\DocumentTemplateAdjusterRepositoryPort;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;

final class BulkDeleteDocumentTemplateAdjusterHandler
{
    public function __construct(
        private readonly DocumentTemplateAdjusterRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteDocumentTemplateAdjusterData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): DocumentTemplateAdjusterId => DocumentTemplateAdjusterId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkDelete($ids);
    }
}

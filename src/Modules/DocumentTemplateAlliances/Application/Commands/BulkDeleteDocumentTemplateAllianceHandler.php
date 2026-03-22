<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Application\Commands;

use Src\Modules\DocumentTemplateAlliances\Application\DTOs\BulkDeleteDocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Domain\Ports\DocumentTemplateAllianceRepositoryPort;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;

final class BulkDeleteDocumentTemplateAllianceHandler
{
    public function __construct(
        private readonly DocumentTemplateAllianceRepositoryPort $repository,
    ) {}

    public function handle(BulkDeleteDocumentTemplateAllianceData $data): int
    {
        $ids = array_map(
            static fn (string $uuid): DocumentTemplateAllianceId => DocumentTemplateAllianceId::fromString($uuid),
            $data->uuids,
        );

        return $this->repository->bulkDelete($ids);
    }
}

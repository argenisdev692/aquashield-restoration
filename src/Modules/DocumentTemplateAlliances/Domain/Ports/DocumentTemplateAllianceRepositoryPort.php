<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Domain\Ports;

use Src\Modules\DocumentTemplateAlliances\Domain\Entities\DocumentTemplateAlliance;
use Src\Modules\DocumentTemplateAlliances\Domain\ValueObjects\DocumentTemplateAllianceId;

interface DocumentTemplateAllianceRepositoryPort
{
    public function find(DocumentTemplateAllianceId $id): ?DocumentTemplateAlliance;

    public function save(DocumentTemplateAlliance $documentTemplateAlliance): void;

    public function delete(DocumentTemplateAllianceId $id): void;

    public function bulkDelete(array $ids): int;
}

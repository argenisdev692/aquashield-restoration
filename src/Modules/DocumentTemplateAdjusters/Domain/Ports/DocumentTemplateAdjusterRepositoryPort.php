<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Domain\Ports;

use Src\Modules\DocumentTemplateAdjusters\Domain\Entities\DocumentTemplateAdjuster;
use Src\Modules\DocumentTemplateAdjusters\Domain\ValueObjects\DocumentTemplateAdjusterId;

interface DocumentTemplateAdjusterRepositoryPort
{
    public function find(DocumentTemplateAdjusterId $id): ?DocumentTemplateAdjuster;

    public function save(DocumentTemplateAdjuster $documentTemplateAdjuster): void;

    public function delete(DocumentTemplateAdjusterId $id): void;

    public function bulkDelete(array $ids): int;
}

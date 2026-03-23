<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Domain\Ports;

use Src\Modules\DocumentTemplates\Domain\Entities\DocumentTemplate;
use Src\Modules\DocumentTemplates\Domain\ValueObjects\DocumentTemplateId;

interface DocumentTemplateRepositoryPort
{
    public function find(DocumentTemplateId $id): ?DocumentTemplate;

    public function save(DocumentTemplate $documentTemplate): void;

    public function delete(DocumentTemplateId $id): void;

    public function bulkDelete(array $ids): int;
}

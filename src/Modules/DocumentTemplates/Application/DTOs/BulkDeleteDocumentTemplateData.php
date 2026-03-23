<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteDocumentTemplateData extends Data
{
    public function __construct(
        /** @var array<int, string> */
        public array $uuids,
    ) {}
}

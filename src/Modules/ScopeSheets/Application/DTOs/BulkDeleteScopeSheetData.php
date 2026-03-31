<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\DTOs;

use Spatie\LaravelData\Data;

class BulkDeleteScopeSheetData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}

<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\DTOs;

use Spatie\LaravelData\Data;

class UpdateScopeSheetData extends Data
{
    public function __construct(
        public ?string $scopeSheetDescription,
        public array $presentations,
        public array $zones,
    ) {}
}

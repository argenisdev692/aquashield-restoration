<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Queries\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ScopeSheets\Application\DTOs\ScopeSheetFilterData;
use Src\Modules\ScopeSheets\Application\Queries\ReadModels\ScopeSheetReadModel;

interface ScopeSheetReadRepository
{
    public function paginate(ScopeSheetFilterData $filters): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?ScopeSheetReadModel;
}

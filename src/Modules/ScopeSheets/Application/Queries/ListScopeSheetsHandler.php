<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\ScopeSheets\Application\DTOs\ScopeSheetFilterData;
use Src\Modules\ScopeSheets\Application\Queries\Contracts\ScopeSheetReadRepository;

final class ListScopeSheetsHandler
{
    public function __construct(
        private readonly ScopeSheetReadRepository $readRepository,
    ) {}

    public function handle(ScopeSheetFilterData $filters): LengthAwarePaginator
    {
        return $this->readRepository->paginate($filters);
    }
}

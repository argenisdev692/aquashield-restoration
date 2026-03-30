<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Queries\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;
use Src\Modules\Claims\Application\Queries\ReadModels\ClaimReadModel;

interface ClaimReadRepository
{
    public function paginate(ClaimFilterData $filters): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?ClaimReadModel;
}

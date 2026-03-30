<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;
use Src\Modules\Claims\Application\Queries\Contracts\ClaimReadRepository;

final class ListClaimsHandler
{
    public function __construct(
        private readonly ClaimReadRepository $readRepository,
    ) {}

    public function handle(ClaimFilterData $filters): LengthAwarePaginator
    {
        return $this->readRepository->paginate($filters);
    }
}

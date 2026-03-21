<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;
use Modules\PublicCompanies\Application\Queries\Contracts\PublicCompanyReadRepository;

final class ListPublicCompaniesHandler
{
    public function __construct(
        private readonly PublicCompanyReadRepository $repository,
    ) {}

    public function handle(PublicCompanyFilterData $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }
}

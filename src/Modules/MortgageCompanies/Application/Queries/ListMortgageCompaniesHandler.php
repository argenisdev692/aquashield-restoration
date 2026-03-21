<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;
use Modules\MortgageCompanies\Application\Queries\Contracts\MortgageCompanyReadRepository;

final class ListMortgageCompaniesHandler
{
    public function __construct(
        private readonly MortgageCompanyReadRepository $readRepository,
    ) {}

    public function handle(MortgageCompanyFilterData $filters): LengthAwarePaginator
    {
        return $this->readRepository->paginate($filters);
    }
}

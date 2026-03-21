<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Queries;

use Modules\MortgageCompanies\Application\Queries\Contracts\MortgageCompanyReadRepository;
use Modules\MortgageCompanies\Application\Queries\ReadModels\MortgageCompanyReadModel;

final class GetMortgageCompanyHandler
{
    public function __construct(
        private readonly MortgageCompanyReadRepository $readRepository,
    ) {}

    public function handle(string $uuid): ?MortgageCompanyReadModel
    {
        return $this->readRepository->findByUuid($uuid);
    }
}

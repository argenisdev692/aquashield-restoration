<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Commands\RestoreMortgageCompany;

use Illuminate\Support\Facades\Cache;
use Modules\MortgageCompanies\Domain\Ports\MortgageCompanyRepositoryPort;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

final readonly class RestoreMortgageCompanyHandler
{
    public function __construct(
        private MortgageCompanyRepositoryPort $repository
    ) {
    }

    public function handle(string $uuid): void
    {
        $id = MortgageCompanyId::fromString($uuid);
        $this->repository->restore($id);

        Cache::forget("mortgage_company_{$uuid}");
        try {
            Cache::tags(['mortgage_companies_list'])->flush();
        } catch (\Exception $e) {
            // Tags not supported
        }
    }
}

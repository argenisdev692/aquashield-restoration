<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Domain\Ports;

use Modules\MortgageCompanies\Domain\Entities\MortgageCompany;
use Modules\MortgageCompanies\Domain\ValueObjects\MortgageCompanyId;

interface MortgageCompanyRepositoryPort
{
    public function find(MortgageCompanyId $id): ?MortgageCompany;

    public function save(MortgageCompany $mortgageCompany): void;

    public function softDelete(MortgageCompanyId $id): void;

    public function restore(MortgageCompanyId $id): void;

    public function list(array $filters, int $page, int $perPage): array;
}

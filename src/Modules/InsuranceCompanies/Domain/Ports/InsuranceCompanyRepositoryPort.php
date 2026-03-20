<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Domain\Ports;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

interface InsuranceCompanyRepositoryPort
{
    public function find(InsuranceCompanyId $id): ?InsuranceCompany;

    public function save(InsuranceCompany $insuranceCompany): void;

    public function softDelete(InsuranceCompanyId $id): void;

    public function restore(InsuranceCompanyId $id): void;

    public function bulkSoftDelete(array $ids): int;
}

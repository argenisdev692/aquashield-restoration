<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Tests\Unit\Application;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\Ports\InsuranceCompanyRepositoryPort;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

final class NullInsuranceCompanyRepository implements InsuranceCompanyRepositoryPort
{
    public function find(InsuranceCompanyId $id): ?InsuranceCompany
    {
        return null;
    }

    public function save(InsuranceCompany $insuranceCompany): void
    {
    }

    public function softDelete(InsuranceCompanyId $id): void
    {
    }

    public function restore(InsuranceCompanyId $id): void
    {
    }

    public function bulkSoftDelete(array $ids): int
    {
        return 0;
    }
}

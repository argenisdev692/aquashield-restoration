<?php

namespace Modules\InsuranceCompanies\Domain\Ports;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;

interface InsuranceCompanyRepositoryPort
{
    public function find(InsuranceCompanyId $id): ?InsuranceCompany;

    public function findByUuid(string $uuid): ?InsuranceCompany;

    public function save(InsuranceCompany $insuranceCompany): void;

    public function delete(InsuranceCompanyId $id): void;

    public function restore(InsuranceCompanyId $id): void;

    public function list(array $filters = []): array;
}

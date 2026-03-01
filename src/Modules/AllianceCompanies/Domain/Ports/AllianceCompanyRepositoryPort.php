<?php

namespace Modules\AllianceCompanies\Domain\Ports;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

interface AllianceCompanyRepositoryPort
{
    public function find(AllianceCompanyId $id): ?AllianceCompany;

    public function findByUuid(string $uuid): ?AllianceCompany;

    public function save(AllianceCompany $AllianceCompany): void;

    public function delete(AllianceCompanyId $id): void;

    public function restore(AllianceCompanyId $id): void;

    public function list(array $filters = []): array;
}

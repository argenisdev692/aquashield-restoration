<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Domain\Ports;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Modules\AllianceCompanies\Domain\ValueObjects\AllianceCompanyId;

interface AllianceCompanyRepositoryPort
{
    public function find(AllianceCompanyId $id): ?AllianceCompany;

    public function save(AllianceCompany $allianceCompany): void;

    public function softDelete(AllianceCompanyId $id): void;

    public function restore(AllianceCompanyId $id): void;

    public function bulkSoftDelete(array $ids): int;
}

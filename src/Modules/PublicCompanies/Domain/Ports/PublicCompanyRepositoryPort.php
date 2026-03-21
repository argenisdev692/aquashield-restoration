<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Domain\Ports;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;

interface PublicCompanyRepositoryPort
{
    public function find(PublicCompanyId $id): ?PublicCompany;

    public function save(PublicCompany $publicCompany): void;

    public function softDelete(PublicCompanyId $id): void;

    public function restore(PublicCompanyId $id): void;
}

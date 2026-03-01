<?php

namespace Modules\PublicCompanies\Domain\Ports;

use Modules\PublicCompanies\Domain\Entities\PublicCompany;
use Modules\PublicCompanies\Domain\ValueObjects\PublicCompanyId;

interface PublicCompanyRepositoryPort
{
    public function find(PublicCompanyId $id): ?PublicCompany;

    public function findByUuid(string $uuid): ?PublicCompany;

    public function save(PublicCompany $PublicCompany): void;

    public function delete(PublicCompanyId $id): void;

    public function restore(PublicCompanyId $id): void;

    public function list(array $filters = []): array;
}

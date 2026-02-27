<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Domain\Ports;

use Src\Contexts\CompanyData\Domain\Entities\CompanyData;
use Src\Contexts\CompanyData\Domain\ValueObjects\CompanyDataId;
use Src\Contexts\CompanyData\Domain\ValueObjects\UserId;

interface CompanyDataRepositoryPort
{
    public function findById(CompanyDataId $id): ?CompanyData;

    public function findByUserId(UserId $userId): ?CompanyData;

    public function save(CompanyData $companyData): void;

    public function delete(CompanyDataId $id): void;

    public function restore(CompanyDataId $id): void;

    public function paginate(\Src\Contexts\CompanyData\Application\DTOs\CompanyDataFilterDTO $filters): array;
}

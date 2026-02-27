<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\UpdateCompanyData;

use Src\Contexts\CompanyData\Application\DTOs\UpdateCompanyDataDTO;

final readonly class UpdateCompanyDataCommand
{
    public function __construct(
        public string $id,
        public UpdateCompanyDataDTO $dto
    ) {
    }
}

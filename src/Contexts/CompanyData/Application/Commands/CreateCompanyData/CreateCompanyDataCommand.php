<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\Commands\CreateCompanyData;

use Src\Contexts\CompanyData\Application\DTOs\CreateCompanyDataDTO;

final readonly class CreateCompanyDataCommand
{
    public function __construct(
        public CreateCompanyDataDTO $dto
    ) {
    }
}

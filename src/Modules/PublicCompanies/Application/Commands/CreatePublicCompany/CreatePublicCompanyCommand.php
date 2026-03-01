<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\CreatePublicCompany;

use Modules\PublicCompanies\Application\DTOs\PublicCompanyDTO;

final readonly class CreatePublicCompanyCommand
{
    public function __construct(
        public PublicCompanyDTO $dto,
    ) {
    }
}

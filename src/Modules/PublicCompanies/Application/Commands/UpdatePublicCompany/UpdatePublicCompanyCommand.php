<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Commands\UpdatePublicCompany;

use Modules\PublicCompanies\Application\DTOs\PublicCompanyDTO;

final readonly class UpdatePublicCompanyCommand
{
    public function __construct(
        public string $uuid,
        public PublicCompanyDTO $dto,
    ) {
    }
}

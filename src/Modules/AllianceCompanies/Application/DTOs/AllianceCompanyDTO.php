<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\DTOs;

use Shared\Application\DTOs\BaseDTO;

final class AllianceCompanyDTO extends BaseDTO
{
    public function __construct(
        public readonly string $AllianceCompanyName,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $website = null,
        public readonly ?int $userId = null,
    ) {
    }
}

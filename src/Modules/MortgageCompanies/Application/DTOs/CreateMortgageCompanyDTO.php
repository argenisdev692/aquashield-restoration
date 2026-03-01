<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\DTOs;

use Spatie\LaravelData\Data;

class CreateMortgageCompanyDTO extends Data
{
    public function __construct(
        public string $mortgageCompanyName,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public int $userId,
    ) {
    }
}

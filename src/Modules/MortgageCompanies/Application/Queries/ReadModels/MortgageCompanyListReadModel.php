<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Application\Queries\ReadModels;

class MortgageCompanyListReadModel
{
    public function __construct(
        public string $uuid,
        public string $mortgageCompanyName,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public string $createdAt,
        public ?string $deletedAt,
    ) {
    }
}

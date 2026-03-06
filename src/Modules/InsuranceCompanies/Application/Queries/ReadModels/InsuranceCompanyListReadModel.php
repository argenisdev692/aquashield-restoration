<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ReadModels;

class InsuranceCompanyListReadModel
{
    public function __construct(
        public string $uuid,
        public string $insuranceCompanyName,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public string $createdAt,
        public ?string $deletedAt,
    ) {
    }
}

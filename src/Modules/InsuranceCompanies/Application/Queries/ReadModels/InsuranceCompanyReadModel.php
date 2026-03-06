<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ReadModels;

class InsuranceCompanyReadModel
{
    public function __construct(
        public string $uuid,
        public string $insuranceCompanyName,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public ?int $userId,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt,
    ) {
    }
}

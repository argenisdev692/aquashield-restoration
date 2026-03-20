<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class InsuranceCompanyListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $insuranceCompanyName,
        public ?string $address,
        public ?string $address2,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public int $userId,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}

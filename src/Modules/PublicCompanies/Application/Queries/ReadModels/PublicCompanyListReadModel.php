<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class PublicCompanyListReadModel extends Data
{
    public function __construct(
        public int $companyId,
        public string $uuid,
        public string $publicCompanyName,
        public ?string $address,
        public ?string $address2,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public ?string $unit,
        public int $userId,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}

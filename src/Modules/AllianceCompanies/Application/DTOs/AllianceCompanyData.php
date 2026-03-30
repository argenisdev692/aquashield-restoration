<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\DTOs;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class AllianceCompanyData extends Data
{
    public function __construct(
        public int $companyId,
        public string $uuid,
        public string $allianceCompanyName,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $website,
        public int $userId,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt,
    ) {}
}

<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class CustomerListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $name,
        public ?string $lastName,
        public string $email,
        public ?string $cellPhone,
        public ?string $homePhone,
        public ?string $occupation,
        public int $userId,
        public ?string $userName,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}

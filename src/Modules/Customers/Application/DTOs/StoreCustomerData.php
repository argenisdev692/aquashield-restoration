<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class StoreCustomerData extends Data
{
    public function __construct(
        public string $name,
        public ?string $lastName,
        public string $email,
        public ?string $cellPhone,
        public ?string $homePhone,
        public ?string $occupation,
        public int $userId,
    ) {}
}

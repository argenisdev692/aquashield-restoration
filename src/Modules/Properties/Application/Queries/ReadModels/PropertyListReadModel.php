<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class PropertyListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $propertyAddress,
        public ?string $propertyAddress2,
        public ?string $propertyState,
        public ?string $propertyCity,
        public ?string $propertyPostalCode,
        public ?string $propertyCountry,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}

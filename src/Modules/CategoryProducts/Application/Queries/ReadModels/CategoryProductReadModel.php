<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class CategoryProductReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $categoryProductName,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}

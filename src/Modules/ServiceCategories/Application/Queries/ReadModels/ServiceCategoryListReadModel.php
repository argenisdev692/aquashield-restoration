<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class ServiceCategoryListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $category,
        public ?string $type,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}

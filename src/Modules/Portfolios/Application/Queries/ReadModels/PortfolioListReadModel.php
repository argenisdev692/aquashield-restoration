<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class PortfolioListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public ?string $projectTypeUuid,
        public ?string $projectTypeTitle,
        public ?string $serviceCategoryName,
        public int $imageCount,
        public ?string $firstImagePath,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}

<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class CauseOfLossReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $causeLossName,
        public ?string $description,
        public string $severity,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt = null,
    ) {}
}

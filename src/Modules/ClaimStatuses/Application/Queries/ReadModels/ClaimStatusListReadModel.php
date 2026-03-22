<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class ClaimStatusListReadModel extends Data
{
    public function __construct(
        public string $uuid,
        public string $claimStatusName,
        public ?string $backgroundColor,
        public string $createdAt,
        public ?string $deletedAt = null,
    ) {}
}

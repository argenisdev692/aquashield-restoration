<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateCauseOfLossData extends Data
{
    public function __construct(
        public string $causeLossName,
        public ?string $description = null,
        public string $severity = 'low',
    ) {}
}

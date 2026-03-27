<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class GenerateCampaignDTO extends Data
{
    public function __construct(
        public string $title,
        public string $niche,
        public string $platform,
    ) {
    }
}

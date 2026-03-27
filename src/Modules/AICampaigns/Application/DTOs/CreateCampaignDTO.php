<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateCampaignDTO extends Data
{
    public function __construct(
        public string  $title,
        public string  $niche,
        public string  $platform,
        public ?string $caption      = null,
        public ?string $hashtags     = null,
        public ?string $callToAction = null,
        public ?string $imagePath    = null,
        public ?string $imageUrl     = null,
        public string  $status       = 'draft',
    ) {
    }
}

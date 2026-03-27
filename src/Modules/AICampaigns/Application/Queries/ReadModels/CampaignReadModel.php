<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Queries\ReadModels;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class CampaignReadModel extends Data
{
    public function __construct(
        public string  $uuid,
        public string  $title,
        public string  $niche,
        public string  $platform,
        public ?string $caption,
        public ?string $hashtags,
        public ?string $callToAction,
        public ?string $imagePath,
        public ?string $imageUrl,
        public string  $status,
        public ?int    $userId,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?string $deletedAt,
    ) {
    }
}

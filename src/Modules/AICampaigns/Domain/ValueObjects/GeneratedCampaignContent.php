<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Domain\ValueObjects;

final readonly class GeneratedCampaignContent
{
    public function __construct(
        public string  $caption,
        public string  $hashtags,
        public string  $callToAction,
        public ?string $imagePath,
        public ?string $imageUrl,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Domain\Entities;

use Modules\AICampaigns\Domain\ValueObjects\CampaignId;
use Shared\Domain\Entities\AggregateRoot;

final class Campaign extends AggregateRoot
{
    public function __construct(
        public CampaignId $id,
        public string     $uuid,
        public string     $title,
        public string     $niche,
        public string     $platform,
        public ?string    $caption      = null,
        public ?string    $hashtags     = null,
        public ?string    $callToAction = null,
        public ?string    $imagePath    = null,
        public ?string    $imageUrl     = null,
        public string     $status       = 'draft',
        public ?int       $userId       = null,
        public ?string    $createdAt    = null,
        public ?string    $updatedAt    = null,
        public ?string    $deletedAt    = null,
    ) {
    }
}

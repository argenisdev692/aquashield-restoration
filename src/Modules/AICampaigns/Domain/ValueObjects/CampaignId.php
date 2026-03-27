<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Domain\ValueObjects;

final readonly class CampaignId
{
    public function __construct(
        public int $value
    ) {
    }
}

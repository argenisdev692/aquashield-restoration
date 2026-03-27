<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Queries\GetCampaign;

final readonly class GetCampaignQuery
{
    public function __construct(
        public string $uuid,
    ) {
    }
}

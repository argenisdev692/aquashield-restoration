<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\RestoreCampaign;

final readonly class RestoreCampaignCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}

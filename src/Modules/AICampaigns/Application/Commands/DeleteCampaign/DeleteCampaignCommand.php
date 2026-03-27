<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\DeleteCampaign;

final readonly class DeleteCampaignCommand
{
    public function __construct(
        public string $uuid,
    ) {
    }
}

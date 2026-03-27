<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\GenerateCampaign;

use Modules\AICampaigns\Application\DTOs\GenerateCampaignDTO;

final readonly class GenerateCampaignCommand
{
    public function __construct(
        public GenerateCampaignDTO $dto,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\CreateCampaign;

use Modules\AICampaigns\Application\DTOs\CreateCampaignDTO;

final readonly class CreateCampaignCommand
{
    public function __construct(
        public CreateCampaignDTO $dto,
    ) {
    }
}

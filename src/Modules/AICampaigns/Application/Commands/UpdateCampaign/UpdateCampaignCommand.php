<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\UpdateCampaign;

use Modules\AICampaigns\Application\DTOs\UpdateCampaignDTO;

final readonly class UpdateCampaignCommand
{
    public function __construct(
        public string          $uuid,
        public UpdateCampaignDTO $dto,
    ) {
    }
}

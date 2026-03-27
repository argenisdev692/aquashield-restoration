<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Queries\ListCampaigns;

use Modules\AICampaigns\Application\DTOs\CampaignFilterDTO;

final readonly class ListCampaignsQuery
{
    public function __construct(
        public CampaignFilterDTO $filters,
    ) {
    }
}

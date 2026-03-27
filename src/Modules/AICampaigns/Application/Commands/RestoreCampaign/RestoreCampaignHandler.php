<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\RestoreCampaign;

use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;

final readonly class RestoreCampaignHandler
{
    public function __construct(
        private CampaignRepositoryPort $repository,
    ) {
    }

    public function handle(RestoreCampaignCommand $command): void
    {
        $this->repository->restore($command->uuid);
    }
}

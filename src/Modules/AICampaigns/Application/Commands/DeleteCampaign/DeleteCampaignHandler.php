<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\DeleteCampaign;

use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;

final readonly class DeleteCampaignHandler
{
    public function __construct(
        private CampaignRepositoryPort $repository,
    ) {
    }

    public function handle(DeleteCampaignCommand $command): void
    {
        $this->repository->softDelete($command->uuid);
    }
}

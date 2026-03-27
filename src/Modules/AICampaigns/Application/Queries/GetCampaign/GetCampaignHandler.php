<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Queries\GetCampaign;

use Modules\AICampaigns\Application\Queries\ReadModels\CampaignReadModel;
use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;

final readonly class GetCampaignHandler
{
    public function __construct(
        private CampaignRepositoryPort $repository,
    ) {
    }

    #[\NoDiscard('Campaign read model must be returned to caller')]
    public function handle(GetCampaignQuery $query): CampaignReadModel
    {
        $campaign = $this->repository->findByUuid($query->uuid);

        return new CampaignReadModel(
            uuid:         $campaign->uuid,
            title:        $campaign->title,
            niche:        $campaign->niche,
            platform:     $campaign->platform,
            caption:      $campaign->caption,
            hashtags:     $campaign->hashtags,
            callToAction: $campaign->callToAction,
            imagePath:    $campaign->imagePath,
            imageUrl:     $campaign->imageUrl,
            status:       $campaign->status,
            userId:       $campaign->userId,
            createdAt:    $campaign->createdAt,
            updatedAt:    $campaign->updatedAt,
            deletedAt:    $campaign->deletedAt,
        );
    }
}

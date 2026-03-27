<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\UpdateCampaign;

use Modules\AICampaigns\Domain\Entities\Campaign;
use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class UpdateCampaignHandler
{
    public function __construct(
        private CampaignRepositoryPort $repository,
        private AuditInterface         $audit,
    ) {
    }

    public function handle(UpdateCampaignCommand $command): Campaign
    {
        $dto  = $command->dto;
        $data = array_filter([
            'title'          => $dto->title,
            'niche'          => $dto->niche,
            'platform'       => $dto->platform,
            'caption'        => $dto->caption,
            'hashtags'       => $dto->hashtags,
            'call_to_action' => $dto->callToAction,
            'image_path'     => $dto->imagePath,
            'image_url'      => $dto->imageUrl,
            'status'         => $dto->status,
        ], fn (mixed $v): bool => $v !== null);

        $campaign = $this->repository->update($command->uuid, $data);

        $this->audit->log(
            logName:     'ai_campaigns.updated',
            description: "AI Campaign updated: {$command->uuid}",
            properties:  ['uuid' => $command->uuid],
        );

        return $campaign;
    }
}

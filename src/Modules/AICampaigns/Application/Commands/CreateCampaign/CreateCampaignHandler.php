<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\CreateCampaign;

use Illuminate\Support\Str;
use Modules\AICampaigns\Domain\Entities\Campaign;
use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class CreateCampaignHandler
{
    public function __construct(
        private CampaignRepositoryPort $repository,
        private AuditInterface         $audit,
    ) {
    }

    public function handle(CreateCampaignCommand $command): Campaign
    {
        $dto  = $command->dto;
        $uuid = Str::uuid()->toString();

        $campaign = $this->repository->create([
            'uuid'            => $uuid,
            'title'           => $dto->title,
            'niche'           => $dto->niche,
            'platform'        => $dto->platform,
            'caption'         => $dto->caption,
            'hashtags'        => $dto->hashtags,
            'call_to_action'  => $dto->callToAction,
            'image_path'      => $dto->imagePath,
            'image_url'       => $dto->imageUrl,
            'status'          => $dto->status,
            'user_id'         => auth()->id(),
        ]);

        $this->audit->log(
            logName:     'ai_campaigns.created',
            description: "AI Campaign created: {$dto->title}",
            properties:  ['uuid' => $uuid, 'platform' => $dto->platform],
        );

        return $campaign;
    }
}

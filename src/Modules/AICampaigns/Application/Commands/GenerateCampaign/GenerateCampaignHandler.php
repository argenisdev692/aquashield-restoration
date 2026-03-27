<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\Commands\GenerateCampaign;

use Illuminate\Support\Str;
use Modules\AICampaigns\Domain\Entities\Campaign;
use Modules\AICampaigns\Domain\Ports\CampaignGenerationPort;
use Modules\AICampaigns\Domain\Ports\CampaignRepositoryPort;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class GenerateCampaignHandler
{
    public function __construct(
        private CampaignGenerationPort $generation,
        private CampaignRepositoryPort $repository,
        private AuditInterface         $audit,
    ) {
    }

    #[\NoDiscard('Generated campaign must be returned to caller')]
    public function handle(GenerateCampaignCommand $command): Campaign
    {
        $dto     = $command->dto;
        $content = $this->generation->generate($dto->title, $dto->niche, $dto->platform);
        $uuid    = Str::uuid()->toString();

        $campaign = $this->repository->create([
            'uuid'           => $uuid,
            'title'          => $dto->title,
            'niche'          => $dto->niche,
            'platform'       => $dto->platform,
            'caption'        => $content->caption,
            'hashtags'       => $content->hashtags,
            'call_to_action' => $content->callToAction,
            'image_path'     => $content->imagePath,
            'image_url'      => $content->imageUrl,
            'status'         => 'generated',
            'user_id'        => auth()->id(),
        ]);

        $this->audit->log(
            logName:     'ai_campaigns.generated',
            description: "AI Campaign generated: {$dto->title} for {$dto->platform}",
            properties:  ['uuid' => $uuid, 'platform' => $dto->platform, 'niche' => $dto->niche],
        );

        return $campaign;
    }
}

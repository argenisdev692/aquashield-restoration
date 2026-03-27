<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Domain\Ports;

use Modules\AICampaigns\Domain\ValueObjects\GeneratedCampaignContent;

interface CampaignGenerationPort
{
    #[\NoDiscard('Generated campaign content must be captured and used')]
    public function generate(string $title, string $niche, string $platform): GeneratedCampaignContent;
}

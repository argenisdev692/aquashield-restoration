<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Domain\Ports;

use Modules\AI\Domain\ValueObjects\GeneratedImage;

interface CampaignImagePort
{
    public function generate(string $prompt, string $topic, string $aspectRatio): ?GeneratedImage;
}

<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\ExternalServices\Ai;

use Illuminate\Support\Facades\Log;
use Modules\AI\Domain\Ports\ResearchPort;
use Modules\AI\Domain\Ports\TextGenerationPort;
use Modules\AICampaigns\Domain\Ports\CampaignGenerationPort;
use Modules\AICampaigns\Domain\Ports\CampaignImagePort;
use Modules\AICampaigns\Domain\ValueObjects\GeneratedCampaignContent;

final readonly class CampaignGenerationAdapter implements CampaignGenerationPort
{
    private const array PLATFORM_ASPECT_RATIOS = [
        'tiktok'    => '9:16',
        'instagram' => '1:1',
        'facebook'  => '16:9',
    ];

    private const array PLATFORM_LABELS = [
        'tiktok'    => 'TikTok',
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
    ];

    public function __construct(
        private TextGenerationPort $textGen,
        private ResearchPort       $research,
        private CampaignImagePort  $imageGen,
    ) {
    }

    public function generate(string $title, string $niche, string $platform): GeneratedCampaignContent
    {
        $platformLabel = self::PLATFORM_LABELS[$platform] ?? $platform;
        $aspectRatio   = self::PLATFORM_ASPECT_RATIOS[$platform] ?? '1:1';

        $researchResult = $this->research->research(
            "trending viral {$platformLabel} content ideas {$niche} 2025 viral posts engagement hooks"
        );

        $textContent = $this->generateTextContent($title, $niche, $platformLabel, $researchResult->summary);

        $imagePrompt = $this->buildImagePrompt($title, $niche, $platformLabel);
        $image       = $this->imageGen->generate($imagePrompt, $title, $aspectRatio);

        Log::info('CampaignGenerationAdapter: campaign generated', [
            'title'    => $title,
            'platform' => $platform,
            'has_image' => $image !== null,
        ]);

        return new GeneratedCampaignContent(
            caption:      $textContent['caption'],
            hashtags:     $textContent['hashtags'],
            callToAction: $textContent['call_to_action'],
            imagePath:    $image?->path,
            imageUrl:     $image?->url,
        );
    }

    private function generateTextContent(
        string $title,
        string $niche,
        string $platform,
        string $researchSummary,
    ): array {
        $systemPrompt = <<<SYSTEM
You are an expert social media content strategist specializing in viral {$platform} campaigns.
You create highly engaging, conversion-focused content that resonates with target audiences.
Always respond with valid JSON only — no markdown, no explanation, no code blocks.
SYSTEM;

        $userPrompt = <<<USER
Campaign title: "{$title}"
Niche: {$niche}
Platform: {$platform}

Research insights on trending topics:
{$researchSummary}

Generate a complete social media campaign post. Return ONLY this JSON structure:
{
  "caption": "<engaging post caption, 150-300 chars, platform-optimized, uses emojis>",
  "hashtags": "<10-15 relevant hashtags separated by spaces, starting with #>",
  "call_to_action": "<compelling CTA, 20-50 chars, action-oriented>"
}
USER;

        $generated = $this->textGen->generate(
            system:    $systemPrompt,
            prompt:    $userPrompt,
            maxTokens: 600,
        );

        return $this->parseTextResponse($generated->content);
    }

    private function buildImagePrompt(string $title, string $niche, string $platform): string
    {
        return "Professional social media marketing image for {$platform}, {$niche} niche, "
            . "campaign: {$title}. Vibrant colors, modern aesthetic, high-impact visual, "
            . "photorealistic, commercial quality, no text overlays.";
    }

    private function parseTextResponse(string $raw): array
    {
        $cleaned = trim($raw);

        if (str_starts_with($cleaned, '```')) {
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);
        }

        $decoded = json_decode(trim($cleaned), associative: true);

        if (!is_array($decoded)) {
            Log::warning('CampaignGenerationAdapter: failed to parse JSON response', ['raw' => $raw]);

            return [
                'caption'         => $cleaned,
                'hashtags'        => '',
                'call_to_action'  => 'Learn more',
            ];
        }

        return [
            'caption'         => (string) ($decoded['caption'] ?? ''),
            'hashtags'        => (string) ($decoded['hashtags'] ?? ''),
            'call_to_action'  => (string) ($decoded['call_to_action'] ?? 'Learn more'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\ExternalServices\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AI\Domain\ValueObjects\GeneratedImage;
use Modules\AICampaigns\Domain\Ports\CampaignImagePort;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

final class CampaignReplicateAdapter implements CampaignImagePort
{
    public function __construct(
        private readonly string                  $apiToken,
        private readonly string                  $baseUrl,
        private readonly string                  $imageModel,
        private readonly string                  $outputFormat,
        private readonly int                     $outputQuality,
        private readonly int                     $safetyTolerance,
        private readonly bool                    $promptUpsampling,
        private readonly int                     $waitSeconds,
        private readonly string                  $storageDisk,
        private readonly string                  $storageDirectory,
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {
    }

    public function generate(string $prompt, string $topic, string $aspectRatio): ?GeneratedImage
    {
        return $this->circuitBreaker->execute(
            serviceName: 'replicate_campaigns',
            action:      fn (): ?GeneratedImage => $this->doGenerate($prompt, $topic, $aspectRatio),
            fallback:    static fn (): null => null,
        );
    }

    private function doGenerate(string $prompt, string $topic, string $aspectRatio): ?GeneratedImage
    {
        [$owner, $name] = array_pad(explode('/', $this->imageModel, 2), 2, '');

        $endpoint = rtrim($this->baseUrl, '/') . "/models/{$owner}/{$name}/predictions";

        Log::info('CampaignReplicateAdapter: creating prediction', [
            'model'        => $this->imageModel,
            'topic'        => $topic,
            'aspect_ratio' => $aspectRatio,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type'  => 'application/json',
            'Prefer'        => 'wait=' . $this->waitSeconds,
        ])
            ->timeout($this->waitSeconds + 15)
            ->post($endpoint, [
                'input' => [
                    'prompt'            => $prompt,
                    'aspect_ratio'      => $aspectRatio,
                    'output_format'     => $this->outputFormat,
                    'output_quality'    => $this->outputQuality,
                    'safety_tolerance'  => $this->safetyTolerance,
                    'prompt_upsampling' => $this->promptUpsampling,
                ],
            ]);

        if ($response->failed()) {
            Log::error('CampaignReplicateAdapter: prediction failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Replicate API error: ' . $response->status());
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'succeeded') {
            Log::warning('CampaignReplicateAdapter: prediction not succeeded', [
                'status' => $data['status'] ?? 'unknown',
                'id'     => $data['id'] ?? null,
            ]);
            return null;
        }

        $remoteUrl = array_first($data['output'] ?? []);

        if (empty($remoteUrl)) {
            Log::warning('CampaignReplicateAdapter: empty output array', ['data' => $data]);
            return null;
        }

        return $this->storeToR2($remoteUrl);
    }

    private function storeToR2(string $remoteUrl): ?GeneratedImage
    {
        $imageContent = Http::timeout(30)->get($remoteUrl)->body();

        if (empty($imageContent)) {
            Log::warning('CampaignReplicateAdapter: could not download generated image', ['url' => $remoteUrl]);
            return null;
        }

        $filename = 'campaign_'
            . now()->format('Ymd_His')
            . '_'
            . Str::random(8)
            . '.'
            . $this->outputFormat;

        $path = trim($this->storageDirectory, '/') . '/' . $filename;

        Storage::disk($this->storageDisk)->put($path, $imageContent);

        $publicUrl = Storage::disk($this->storageDisk)->url($path);

        Log::info('CampaignReplicateAdapter: image stored', ['path' => $path, 'url' => $publicUrl]);

        return new GeneratedImage(
            url:  $publicUrl,
            disk: $this->storageDisk,
            path: $path,
        );
    }
}

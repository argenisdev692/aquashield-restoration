<?php

declare(strict_types=1);

namespace Modules\AI\Infrastructure\Anthropic;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AI\Domain\Ports\TextGenerationPort;
use Modules\AI\Domain\ValueObjects\GeneratedText;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

final class AnthropicAdapter implements TextGenerationPort
{
    public function __construct(
        private readonly string                  $apiKey,
        private readonly string                  $apiUrl,
        private readonly string                  $version,
        private readonly string                  $model,
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {
    }

    #[\NoDiscard('Generated text must be captured and used')]
    public function generate(string $system, string $prompt, int $maxTokens = 2048): GeneratedText
    {
        return $this->circuitBreaker->execute(
            serviceName: 'anthropic',
            action:      fn (): GeneratedText => $this->doGenerate($system, $prompt, $maxTokens),
            fallback:    static fn (): never => throw new \RuntimeException(
                'Anthropic API unavailable — circuit open or repeated failure',
            ),
        );
    }

    private function doGenerate(string $system, string $prompt, int $maxTokens): GeneratedText
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => $this->version,
            'Content-Type'      => 'application/json',
        ])
            ->timeout(180)
            ->post($this->apiUrl, [
                'model'      => $this->model,
                'max_tokens' => $maxTokens,
                'system'     => $system,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            Log::error('AnthropicAdapter: API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'model'  => $this->model,
            ]);

            throw new \RuntimeException('Anthropic API error: ' . $response->status());
        }

        $data = $response->json();

        return new GeneratedText(
            content: (string) ($data['content'][0]['text'] ?? ''),
        );
    }
}

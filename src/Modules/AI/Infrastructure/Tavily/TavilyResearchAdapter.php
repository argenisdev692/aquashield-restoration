<?php

declare(strict_types=1);

namespace Modules\AI\Infrastructure\Tavily;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AI\Domain\Ports\ResearchPort;
use Modules\AI\Domain\ValueObjects\ResearchResult;
use Shared\Infrastructure\Resilience\CircuitBreaker\CircuitBreakerInterface;

final class TavilyResearchAdapter implements ResearchPort
{
    public function __construct(
        private readonly string                  $apiKey,
        private readonly string                  $searchUrl,
        private readonly string                  $searchDepth,
        private readonly int                     $maxResults,
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {
    }

    #[\NoDiscard('Research result must be captured and used')]
    public function research(string $query): ResearchResult
    {
        if (empty($this->apiKey)) {
            Log::warning('TavilyResearchAdapter: no API key configured, returning empty result');
            return ResearchResult::empty();
        }

        return $this->circuitBreaker->execute(
            serviceName: 'tavily',
            action:      fn (): ResearchResult => $this->doResearch($query),
            fallback:    static fn (): ResearchResult => ResearchResult::empty(),
        );
    }

    private function doResearch(string $query): ResearchResult
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->post($this->searchUrl, [
                'api_key'         => $this->apiKey,
                'query'           => $query,
                'search_depth'    => $this->searchDepth,
                'include_answer'  => true,
                'max_results'     => $this->maxResults,
                'exclude_domains' => ['pinterest.com', 'quora.com'],
            ]);

        if ($response->failed()) {
            Log::warning('TavilyResearchAdapter: request failed', [
                'status' => $response->status(),
                'query'  => $query,
            ]);

            throw new \RuntimeException('Tavily API error: ' . $response->status());
        }

        $data = $response->json();

        $sources = collect($data['results'] ?? [])
            ->map(fn (array $r): array => [
                'title'   => (string) ($r['title'] ?? ''),
                'url'     => (string) ($r['url'] ?? ''),
                'snippet' => (string) ($r['content'] ?? ''),
                'score'   => (float) ($r['score'] ?? 0.0),
            ])
            ->sortByDesc('score')
            ->values()
            ->toArray();

        return new ResearchResult(
            sources: $sources,
            summary: (string) ($data['answer'] ?? ''),
        );
    }
}

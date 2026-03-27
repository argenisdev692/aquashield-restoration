<?php

declare(strict_types=1);

namespace Modules\AI\Domain\ValueObjects;

final readonly class ResearchResult
{
    /**
     * @param array<int, array{title: string, url: string, snippet: string, score: float}> $sources
     */
    public function __construct(
        public array  $sources,
        public string $summary,
    ) {
    }

    public function isEmpty(): bool
    {
        return empty($this->sources);
    }

    public static function empty(): self
    {
        return new self(sources: [], summary: '');
    }
}

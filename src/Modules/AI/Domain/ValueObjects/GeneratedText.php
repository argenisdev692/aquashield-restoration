<?php

declare(strict_types=1);

namespace Modules\AI\Domain\ValueObjects;

final readonly class GeneratedText
{
    public function __construct(
        public string $content,
    ) {
    }

    public function isEmpty(): bool
    {
        return trim($this->content) === '';
    }
}

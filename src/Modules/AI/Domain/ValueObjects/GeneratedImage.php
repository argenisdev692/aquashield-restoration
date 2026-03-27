<?php

declare(strict_types=1);

namespace Modules\AI\Domain\ValueObjects;

final readonly class GeneratedImage
{
    public function __construct(
        public string $url,
        public string $disk,
        public string $path,
    ) {
    }
}

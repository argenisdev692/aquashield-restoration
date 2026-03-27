<?php

declare(strict_types=1);

namespace Modules\AI\Domain\Ports;

use Modules\AI\Domain\ValueObjects\GeneratedImage;

interface ImageGenerationPort
{
    /**
     * Generate an image for the given prompt.
     * Returns null when the provider is unavailable or generation fails —
     * the caller must allow the user to upload the image manually.
     */
    public function generate(string $prompt, string $topic): ?GeneratedImage;
}

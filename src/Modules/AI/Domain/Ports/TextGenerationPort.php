<?php

declare(strict_types=1);

namespace Modules\AI\Domain\Ports;

use Modules\AI\Domain\ValueObjects\GeneratedText;

interface TextGenerationPort
{
    #[\NoDiscard('Generated text must be captured and used')]
    public function generate(string $system, string $prompt, int $maxTokens = 2048): GeneratedText;
}

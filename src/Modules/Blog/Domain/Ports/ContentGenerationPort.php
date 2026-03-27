<?php

declare(strict_types=1);

namespace Modules\Blog\Domain\Ports;

use Modules\Blog\Domain\ValueObjects\GeneratedPostContent;

interface ContentGenerationPort
{
    public function generate(string $topic, string $niche, int $wordCount): GeneratedPostContent;
}

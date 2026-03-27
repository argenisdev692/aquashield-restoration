<?php

declare(strict_types=1);

namespace Modules\Blog\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class GeneratePostContentDTO extends Data
{
    public function __construct(
        public string $topic,
        public string $niche,
        public int $wordCount = 1200,
    ) {
    }
}

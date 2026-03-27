<?php

declare(strict_types=1);

namespace Modules\Blog\Domain\ValueObjects;

final readonly class GeneratedPostContent
{
    public function __construct(
        public string $postContent,
        public string $postTitleSlug,
        public string $postExcerpt,
        public string $metaTitle,
        public string $metaDescription,
        public string $metaKeywords,
        public array $sources,
    ) {
    }
}

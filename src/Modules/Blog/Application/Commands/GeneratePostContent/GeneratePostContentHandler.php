<?php

declare(strict_types=1);

namespace Modules\Blog\Application\Commands\GeneratePostContent;

use Modules\Blog\Domain\Ports\ContentGenerationPort;
use Modules\Blog\Domain\ValueObjects\GeneratedPostContent;

final readonly class GeneratePostContentHandler
{
    public function __construct(
        private ContentGenerationPort $pipeline,
    ) {
    }

    #[\NoDiscard('Generated post content must be returned to caller')]
    public function handle(GeneratePostContentCommand $command): GeneratedPostContent
    {
        return $this->pipeline->generate(
            topic: $command->dto->topic,
            niche: $command->dto->niche,
            wordCount: $command->dto->wordCount,
        );
    }
}

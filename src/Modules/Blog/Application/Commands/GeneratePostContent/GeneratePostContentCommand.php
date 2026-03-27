<?php

declare(strict_types=1);

namespace Modules\Blog\Application\Commands\GeneratePostContent;

use Modules\Blog\Application\DTOs\GeneratePostContentDTO;

final readonly class GeneratePostContentCommand
{
    public function __construct(
        public GeneratePostContentDTO $dto,
    ) {
    }
}

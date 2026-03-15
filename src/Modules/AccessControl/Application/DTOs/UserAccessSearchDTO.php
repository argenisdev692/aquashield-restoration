<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\DTOs;

use Spatie\LaravelData\Data;

final class UserAccessSearchDTO extends Data
{
    public function __construct(
        public ?string $search = null,
        public int $limit = 10,
    ) {
    }
}

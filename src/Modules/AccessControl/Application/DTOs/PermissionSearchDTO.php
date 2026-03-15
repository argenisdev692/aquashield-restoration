<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\DTOs;

use Spatie\LaravelData\Data;

final class PermissionSearchDTO extends Data
{
    public function __construct(
        public ?string $search = null,
    ) {
    }
}

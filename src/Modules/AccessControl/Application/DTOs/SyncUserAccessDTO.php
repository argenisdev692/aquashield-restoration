<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\DTOs;

use Spatie\LaravelData\Data;

final class SyncUserAccessDTO extends Data
{
    public function __construct(
        public array $roles = [],
        public array $permissions = [],
    ) {
    }
}

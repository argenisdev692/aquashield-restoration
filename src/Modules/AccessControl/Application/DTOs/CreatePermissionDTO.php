<?php

declare(strict_types=1);

namespace Modules\AccessControl\Application\DTOs;

use Spatie\LaravelData\Data;

final class CreatePermissionDTO extends Data
{
    public function __construct(
        public string $name,
    ) {
    }
}

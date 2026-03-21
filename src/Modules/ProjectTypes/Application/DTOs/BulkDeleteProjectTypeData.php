<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteProjectTypeData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}

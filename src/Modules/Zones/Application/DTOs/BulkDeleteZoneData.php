<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteZoneData extends Data
{
    public function __construct(
        /** @var string[] */
        public array $uuids,
    ) {}
}

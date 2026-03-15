<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteCauseOfLossData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}

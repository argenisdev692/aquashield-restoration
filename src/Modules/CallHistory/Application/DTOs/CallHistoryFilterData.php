<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\DTOs;

use Spatie\LaravelData\Data;

final class CallHistoryFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $direction = null,
        public ?string $callType = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {
    }
}

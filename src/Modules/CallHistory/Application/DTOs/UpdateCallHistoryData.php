<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\DTOs;

use Spatie\LaravelData\Data;

final class UpdateCallHistoryData extends Data
{
    public function __construct(
        public ?string $agentName = null,
        public ?string $callStatus = null,
        public ?string $callType = null,
    ) {
    }
}

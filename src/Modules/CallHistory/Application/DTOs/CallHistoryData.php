<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class CallHistoryData extends Data
{
    public function __construct(
        public string $uuid,
        public string $callId,
        public ?string $agentId = null,
        public ?string $agentName = null,
        public ?string $fromNumber = null,
        public ?string $toNumber = null,
        public string $direction = 'inbound',
        public string $callStatus = 'registered',
        public ?string $startTimestamp = null,
        public ?string $endTimestamp = null,
        public ?int $durationMs = null,
        public ?string $transcript = null,
        public ?string $recordingUrl = null,
        public ?array $callAnalysis = null,
        public ?string $disconnectionReason = null,
        public ?array $metadata = null,
        public string $callType = 'other',
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {
    }
}

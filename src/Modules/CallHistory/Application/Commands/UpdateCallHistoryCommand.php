<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Commands;

use Modules\CallHistory\Application\DTOs\UpdateCallHistoryData;
use Modules\CallHistory\Domain\Entities\CallHistory;
use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;

final readonly class UpdateCallHistoryCommand
{
    public function __construct(
        private CallHistoryRepositoryPort $repository
    ) {
    }

    public function execute(string $uuid, UpdateCallHistoryData $data): CallHistory
    {
        $callHistory = $this->repository->findByUuid(new CallHistoryId($uuid));
        if ($callHistory === null) {
            throw new \DomainException("Call history with UUID {$uuid} not found");
        }

        // Create updated entity - since entity is immutable in some fields,
        // we create a new one with updated values
        $updated = new CallHistory(
            uuid: $callHistory->uuid(),
            callId: $callHistory->callId(),
            agentId: $callHistory->agentId(),
            agentName: $data->agentName ?? $callHistory->agentName(),
            fromNumber: $callHistory->fromNumber(),
            toNumber: $callHistory->toNumber(),
            direction: $callHistory->direction(),
            callStatus: $data->callStatus ?? $callHistory->callStatus(),
            startTimestamp: $callHistory->startTimestamp(),
            endTimestamp: $callHistory->endTimestamp(),
            durationMs: $callHistory->durationMs(),
            transcript: $callHistory->transcript(),
            recordingUrl: $callHistory->recordingUrl(),
            callAnalysis: $callHistory->callAnalysis(),
            disconnectionReason: $callHistory->disconnectionReason(),
            metadata: $callHistory->metadata(),
            callType: $data->callType ?? $callHistory->callType(),
            createdAt: $callHistory->createdAt(),
            updatedAt: new \DateTimeImmutable(),
            deletedAt: $callHistory->deletedAt(),
        );

        $this->repository->update($updated);

        return $updated;
    }
}

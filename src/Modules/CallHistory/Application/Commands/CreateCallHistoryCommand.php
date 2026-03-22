<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Commands;

use DateTimeImmutable;
use Modules\CallHistory\Application\DTOs\CreateCallHistoryData;
use Modules\CallHistory\Domain\Entities\CallHistory;
use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;
use Ramsey\Uuid\Uuid;

final readonly class CreateCallHistoryCommand
{
    public function __construct(
        private CallHistoryRepositoryPort $repository
    ) {
    }

    public function execute(CreateCallHistoryData $data): CallHistory
    {
        $existing = $this->repository->findByCallIdWithTrashed($data->callId);
        if ($existing !== null) {
            throw new \DomainException("Call with ID {$data->callId} already exists");
        }

        $callHistory = new CallHistory(
            uuid: new CallHistoryId(Uuid::uuid4()->toString()),
            callId: $data->callId,
            agentId: $data->agentId,
            agentName: $data->agentName,
            fromNumber: $data->fromNumber,
            toNumber: $data->toNumber,
            direction: $data->direction,
            callStatus: $data->callStatus,
            startTimestamp: $data->startTimestamp ? new DateTimeImmutable($data->startTimestamp) : null,
            endTimestamp: $data->endTimestamp ? new DateTimeImmutable($data->endTimestamp) : null,
            durationMs: $data->durationMs,
            transcript: $data->transcript,
            recordingUrl: $data->recordingUrl,
            callAnalysis: $data->callAnalysis,
            disconnectionReason: $data->disconnectionReason,
            metadata: $data->metadata,
            callType: $data->callType,
        );

        $this->repository->save($callHistory);

        return $callHistory;
    }
}

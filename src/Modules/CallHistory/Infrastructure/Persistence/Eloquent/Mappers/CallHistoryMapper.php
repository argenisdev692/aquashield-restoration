<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Persistence\Eloquent\Mappers;

use DateTimeImmutable;
use Modules\CallHistory\Domain\Entities\CallHistory;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;
use Modules\CallHistory\Infrastructure\Persistence\Eloquent\Models\CallHistoryEloquentModel;

final class CallHistoryMapper
{
    public function toDomain(CallHistoryEloquentModel $model): CallHistory
    {
        return new CallHistory(
            uuid: new CallHistoryId($model->uuid),
            callId: $model->call_id,
            agentId: $model->agent_id,
            agentName: $model->agent_name,
            fromNumber: $model->from_number,
            toNumber: $model->to_number,
            direction: $model->direction,
            callStatus: $model->call_status,
            startTimestamp: $model->start_timestamp ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $model->start_timestamp->format('Y-m-d H:i:s')) : null,
            endTimestamp: $model->end_timestamp ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $model->end_timestamp->format('Y-m-d H:i:s')) : null,
            durationMs: $model->duration_ms,
            transcript: $model->transcript,
            recordingUrl: $model->recording_url,
            callAnalysis: $model->call_analysis,
            disconnectionReason: $model->disconnection_reason,
            metadata: $model->metadata,
            callType: $model->call_type,
            createdAt: $model->created_at ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $model->created_at->format('Y-m-d H:i:s')) : null,
            updatedAt: $model->updated_at ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $model->updated_at->format('Y-m-d H:i:s')) : null,
            deletedAt: $model->deleted_at ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $model->deleted_at->format('Y-m-d H:i:s')) : null,
        );
    }

    public function toEloquent(CallHistory $entity): CallHistoryEloquentModel
    {
        $model = new CallHistoryEloquentModel();

        $model->uuid = $entity->uuid()->value();
        $model->call_id = $entity->callId();
        $model->agent_id = $entity->agentId();
        $model->agent_name = $entity->agentName();
        $model->from_number = $entity->fromNumber();
        $model->to_number = $entity->toNumber();
        $model->direction = $entity->direction();
        $model->call_status = $entity->callStatus();
        $model->start_timestamp = $entity->startTimestamp();
        $model->end_timestamp = $entity->endTimestamp();
        $model->duration_ms = $entity->durationMs();
        $model->transcript = $entity->transcript();
        $model->recording_url = $entity->recordingUrl();
        $model->call_analysis = $entity->callAnalysis();
        $model->disconnection_reason = $entity->disconnectionReason();
        $model->metadata = $entity->metadata();
        $model->call_type = $entity->callType();

        return $model;
    }

    public function updateEloquent(CallHistory $entity, CallHistoryEloquentModel $model): void
    {
        $model->agent_id = $entity->agentId();
        $model->agent_name = $entity->agentName();
        $model->from_number = $entity->fromNumber();
        $model->to_number = $entity->toNumber();
        $model->direction = $entity->direction();
        $model->call_status = $entity->callStatus();
        $model->start_timestamp = $entity->startTimestamp();
        $model->end_timestamp = $entity->endTimestamp();
        $model->duration_ms = $entity->durationMs();
        $model->transcript = $entity->transcript();
        $model->recording_url = $entity->recordingUrl();
        $model->call_analysis = $entity->callAnalysis();
        $model->disconnection_reason = $entity->disconnectionReason();
        $model->metadata = $entity->metadata();
        $model->call_type = $entity->callType();
    }
}

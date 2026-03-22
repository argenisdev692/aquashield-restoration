<?php

declare(strict_types=1);

namespace Modules\CallHistory\Domain\Entities;

use DateTimeImmutable;
use DateTimeInterface;
use Modules\CallHistory\Domain\ValueObjects\CallHistoryId;

final class CallHistory
{
    private CallHistoryId $uuid;
    private string $callId;
    private ?string $agentId;
    private ?string $agentName;
    private ?string $fromNumber;
    private ?string $toNumber;
    private string $direction;
    private string $callStatus;
    private ?DateTimeImmutable $startTimestamp;
    private ?DateTimeImmutable $endTimestamp;
    private ?int $durationMs;
    private ?string $transcript;
    private ?string $recordingUrl;
    private ?array $callAnalysis;
    private ?string $disconnectionReason;
    private ?array $metadata;
    private string $callType;
    private ?DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;
    private ?DateTimeImmutable $deletedAt;

    public function __construct(
        CallHistoryId $uuid,
        string $callId,
        ?string $agentId = null,
        ?string $agentName = null,
        ?string $fromNumber = null,
        ?string $toNumber = null,
        string $direction = 'inbound',
        string $callStatus = 'registered',
        ?DateTimeImmutable $startTimestamp = null,
        ?DateTimeImmutable $endTimestamp = null,
        ?int $durationMs = null,
        ?string $transcript = null,
        ?string $recordingUrl = null,
        ?array $callAnalysis = null,
        ?string $disconnectionReason = null,
        ?array $metadata = null,
        string $callType = 'other',
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
        ?DateTimeImmutable $deletedAt = null
    ) {
        $this->uuid = $uuid;
        $this->callId = $callId;
        $this->agentId = $agentId;
        $this->agentName = $agentName;
        $this->fromNumber = $fromNumber;
        $this->toNumber = $toNumber;
        $this->direction = $direction;
        $this->callStatus = $callStatus;
        $this->startTimestamp = $startTimestamp;
        $this->endTimestamp = $endTimestamp;
        $this->durationMs = $durationMs;
        $this->transcript = $transcript;
        $this->recordingUrl = $recordingUrl;
        $this->callAnalysis = $callAnalysis;
        $this->disconnectionReason = $disconnectionReason;
        $this->metadata = $metadata;
        $this->callType = $callType;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    public function uuid(): CallHistoryId
    {
        return $this->uuid;
    }

    public function callId(): string
    {
        return $this->callId;
    }

    public function agentId(): ?string
    {
        return $this->agentId;
    }

    public function agentName(): ?string
    {
        return $this->agentName;
    }

    public function fromNumber(): ?string
    {
        return $this->fromNumber;
    }

    public function toNumber(): ?string
    {
        return $this->toNumber;
    }

    public function direction(): string
    {
        return $this->direction;
    }

    public function callStatus(): string
    {
        return $this->callStatus;
    }

    public function startTimestamp(): ?DateTimeImmutable
    {
        return $this->startTimestamp;
    }

    public function endTimestamp(): ?DateTimeImmutable
    {
        return $this->endTimestamp;
    }

    public function durationMs(): ?int
    {
        return $this->durationMs;
    }

    public function transcript(): ?string
    {
        return $this->transcript;
    }

    public function recordingUrl(): ?string
    {
        return $this->recordingUrl;
    }

    public function callAnalysis(): ?array
    {
        return $this->callAnalysis;
    }

    public function disconnectionReason(): ?string
    {
        return $this->disconnectionReason;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }

    public function callType(): string
    {
        return $this->callType;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function markAsDeleted(): void
    {
        $this->deletedAt = new DateTimeImmutable();
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public function updateFromRetellData(array $data): void
    {
        if (isset($data['agent_id'])) {
            $this->agentId = $data['agent_id'];
        }
        if (isset($data['agent_name'])) {
            $this->agentName = $data['agent_name'];
        }
        if (isset($data['from_number'])) {
            $this->fromNumber = $data['from_number'];
        }
        if (isset($data['to_number'])) {
            $this->toNumber = $data['to_number'];
        }
        if (isset($data['direction'])) {
            $this->direction = $data['direction'];
        }
        if (isset($data['call_status'])) {
            $this->callStatus = $data['call_status'];
        }
        if (isset($data['start_timestamp'])) {
            $this->startTimestamp = DateTimeImmutable::createFromFormat('U', (string) (intdiv($data['start_timestamp'], 1000)));
        }
        if (isset($data['end_timestamp'])) {
            $this->endTimestamp = DateTimeImmutable::createFromFormat('U', (string) (intdiv($data['end_timestamp'], 1000)));
        }
        if (isset($data['duration_ms'])) {
            $this->durationMs = $data['duration_ms'];
        }
        if (isset($data['transcript'])) {
            $this->transcript = $data['transcript'];
        }
        if (isset($data['recording_url'])) {
            $this->recordingUrl = $data['recording_url'];
        }
        if (isset($data['call_analysis'])) {
            $this->callAnalysis = $data['call_analysis'];
        }
        if (isset($data['disconnection_reason'])) {
            $this->disconnectionReason = $data['disconnection_reason'];
        }
        if (isset($data['metadata'])) {
            $this->metadata = $data['metadata'];
        }
    }
}

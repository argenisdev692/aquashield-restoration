<?php

declare(strict_types=1);

namespace Modules\CallHistory\Application\Commands;

use DateTimeImmutable;
use Modules\CallHistory\Application\DTOs\CreateCallHistoryData;
use Modules\CallHistory\Domain\Ports\CallHistoryRepositoryPort;
use Modules\CallHistory\Infrastructure\Services\RetellAIService;

final readonly class SyncCallsFromRetellCommand
{
    public function __construct(
        private CallHistoryRepositoryPort $repository,
        private RetellAIService $retellService,
        private CreateCallHistoryCommand $createCommand,
        private UpdateCallHistoryCommand $updateCommand,
    ) {
    }

    /**
     * Sync calls from Retell AI API to local database
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters = []): array
    {
        $calls = $this->retellService->listCalls($filters);

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($calls as $callData) {
            try {
                $existing = $this->repository->findByCallIdWithTrashed($callData['call_id']);

                if ($existing === null) {
                    // Create new call history
                    $createData = new CreateCallHistoryData(
                        callId: $callData['call_id'],
                        agentId: $callData['agent_id'] ?? null,
                        agentName: $callData['agent_name'] ?? null,
                        fromNumber: $callData['from_number'] ?? null,
                        toNumber: $callData['to_number'] ?? null,
                        direction: $callData['direction'] ?? 'inbound',
                        callStatus: $callData['call_status'] ?? 'registered',
                        startTimestamp: isset($callData['start_timestamp'])
                            ? date('Y-m-d H:i:s', (int) ($callData['start_timestamp'] / 1000))
                            : null,
                        endTimestamp: isset($callData['end_timestamp'])
                            ? date('Y-m-d H:i:s', (int) ($callData['end_timestamp'] / 1000))
                            : null,
                        durationMs: $callData['duration_ms'] ?? null,
                        transcript: $callData['transcript'] ?? null,
                        recordingUrl: $callData['recording_url'] ?? null,
                        callAnalysis: $callData['call_analysis'] ?? null,
                        disconnectionReason: $callData['disconnection_reason'] ?? null,
                        metadata: $callData['metadata'] ?? null,
                        callType: $this->detectCallType($callData),
                    );

                    $this->createCommand->execute($createData);
                    $created++;
                } else {
                    // Update existing call history
                    $existing->updateFromRetellData($callData);
                    $this->repository->update($existing);
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'call_id' => $callData['call_id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($calls),
            'errors' => $errors,
        ];
    }

    /**
     * Detect call type based on call data
     */
    private function detectCallType(array $callData): string
    {
        $metadata = $callData['metadata'] ?? [];
        $transcript = strtolower($callData['transcript'] ?? '');

        if (isset($metadata['call_type'])) {
            return $metadata['call_type'];
        }

        if (str_contains($transcript, 'appointment') || str_contains($transcript, 'schedule')) {
            return 'appointment';
        }

        if (str_contains($transcript, 'lead') || str_contains($transcript, 'interested')) {
            return 'lead';
        }

        if (str_contains($transcript, 'support') || str_contains($transcript, 'help')) {
            return 'support';
        }

        return 'other';
    }
}

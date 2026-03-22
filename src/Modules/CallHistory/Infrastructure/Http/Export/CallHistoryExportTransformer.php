<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Modules\CallHistory\Infrastructure\Persistence\Eloquent\Models\CallHistoryEloquentModel;

final class CallHistoryExportTransformer
{
    #[\NoDiscard]
    public static function transformForExcel(CallHistoryEloquentModel $call): array
    {
        return $call
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard]
    public static function transformForPdf(CallHistoryEloquentModel $call): array
    {
        return $call
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...);
    }

    private static function extractPayload(CallHistoryEloquentModel $call): array
    {
        return [
            'call_id' => $call->call_id,
            'agent_name' => $call->agent_name,
            'from_number' => $call->from_number,
            'to_number' => $call->to_number,
            'direction' => $call->direction,
            'call_status' => $call->call_status,
            'call_type' => $call->call_type,
            'start_timestamp' => $call->start_timestamp?->toIso8601String(),
            'end_timestamp' => $call->end_timestamp?->toIso8601String(),
            'duration_ms' => $call->duration_ms,
            'disconnection_reason' => $call->disconnection_reason,
            'created_at' => $call->created_at?->toIso8601String(),
            'deleted_at' => $call->deleted_at?->toIso8601String(),
        ];
    }

    private static function formatDates(array $payload): array
    {
        foreach (['start_timestamp', 'end_timestamp', 'created_at', 'deleted_at'] as $field) {
            $payload[$field] = $payload[$field] !== null
                ? CarbonImmutable::parse($payload[$field])->format('M j, Y H:i')
                : '—';
        }

        return $payload;
    }

    private static function sanitizePayload(array $payload): array
    {
        foreach (['call_id', 'agent_name', 'from_number', 'to_number', 'direction', 'call_status', 'call_type', 'disconnection_reason'] as $field) {
            $payload[$field] = $payload[$field] ?? '—';

            if ($payload[$field] === '') {
                $payload[$field] = '—';
            }
        }

        $payload['duration_formatted'] = $payload['duration_ms'] !== null && $payload['duration_ms'] > 0
            ? self::formatDuration((int) $payload['duration_ms'])
            : '—';

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['call_id'],
            $payload['agent_name'],
            $payload['from_number'],
            $payload['to_number'],
            ucfirst($payload['direction']),
            ucfirst(str_replace('_', ' ', $payload['call_status'])),
            ucfirst(str_replace('_', ' ', $payload['call_type'])),
            $payload['start_timestamp'],
            $payload['duration_formatted'],
            $payload['disconnection_reason'],
            $payload['created_at'],
        ];
    }

    private static function formatDuration(int $ms): string
    {
        $seconds = (int) floor($ms / 1000);
        $minutes = (int) floor($seconds / 60);
        $hours = (int) floor($minutes / 60);

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes % 60, $seconds % 60);
        }

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds % 60);
        }

        return sprintf('%ds', $seconds);
    }
}

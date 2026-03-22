<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;

final class ServiceRequestExportTransformer
{
    #[\NoDiscard]
    public static function transformForExcel(ServiceRequestEloquentModel $serviceRequest): array
    {
        return $serviceRequest
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard]
    public static function transformForPdf(ServiceRequestEloquentModel $serviceRequest): array
    {
        return $serviceRequest
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...);
    }

    private static function extractPayload(ServiceRequestEloquentModel $serviceRequest): array
    {
        return [
            'requested_service' => $serviceRequest->requested_service,
            'status' => $serviceRequest->deleted_at !== null ? 'Suspended' : 'Active',
            'created_at' => $serviceRequest->created_at?->toIso8601String(),
            'deleted_at' => $serviceRequest->deleted_at?->toIso8601String(),
        ];
    }

    private static function formatDates(array $payload): array
    {
        foreach (['created_at', 'deleted_at'] as $field) {
            $payload[$field] = $payload[$field] !== null
                ? CarbonImmutable::parse($payload[$field])->format('F j, Y')
                : '—';
        }

        return $payload;
    }

    private static function sanitizePayload(array $payload): array
    {
        foreach (['requested_service'] as $field) {
            $payload[$field] = $payload[$field] ?? '—';

            if ($payload[$field] === '') {
                $payload[$field] = '—';
            }
        }

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['requested_service'],
            $payload['status'],
            $payload['created_at'],
            $payload['deleted_at'],
        ];
    }
}

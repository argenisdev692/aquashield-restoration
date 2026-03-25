<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;

final class PropertyExportTransformer
{
    #[\NoDiscard]
    public static function transformForExcel(PropertyEloquentModel $property): array
    {
        return $property
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard]
    public static function transformForPdf(PropertyEloquentModel $property): array
    {
        return $property
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...);
    }

    private static function extractPayload(PropertyEloquentModel $property): array
    {
        return [
            'property_address'     => $property->property_address,
            'property_address_2'   => $property->property_address_2,
            'property_state'       => $property->property_state,
            'property_city'        => $property->property_city,
            'property_postal_code' => $property->property_postal_code,
            'property_country'     => $property->property_country,
            'property_latitude'    => $property->property_latitude,
            'property_longitude'   => $property->property_longitude,
            'status'               => $property->deleted_at !== null ? 'Suspended' : 'Active',
            'created_at'           => $property->created_at?->toIso8601String(),
            'deleted_at'           => $property->deleted_at?->toIso8601String(),
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
        $fields = [
            'property_address', 'property_address_2', 'property_state',
            'property_city', 'property_postal_code', 'property_country',
            'property_latitude', 'property_longitude',
        ];

        foreach ($fields as $field) {
            if (($payload[$field] ?? '') === '') {
                $payload[$field] = '—';
            }
        }

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['property_address'],
            $payload['property_address_2'],
            $payload['property_state'],
            $payload['property_city'],
            $payload['property_postal_code'],
            $payload['property_country'],
            $payload['property_latitude'],
            $payload['property_longitude'],
            $payload['status'],
            $payload['created_at'],
            $payload['deleted_at'],
        ];
    }
}

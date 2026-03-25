<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;

final class CustomerExportTransformer
{
    #[\NoDiscard]
    public static function transformForExcel(CustomerEloquentModel $customer): array
    {
        return $customer
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard]
    public static function transformForPdf(CustomerEloquentModel $customer): array
    {
        return $customer
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...);
    }

    private static function extractPayload(CustomerEloquentModel $customer): array
    {
        return [
            'name'       => $customer->name,
            'last_name'  => $customer->last_name,
            'email'      => $customer->email,
            'cell_phone' => $customer->cell_phone,
            'home_phone' => $customer->home_phone,
            'occupation' => $customer->occupation,
            'user_name'  => $customer->user?->name,
            'status'     => $customer->deleted_at !== null ? 'Suspended' : 'Active',
            'created_at' => $customer->created_at?->toIso8601String(),
            'deleted_at' => $customer->deleted_at?->toIso8601String(),
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
        foreach (['name', 'last_name', 'email', 'cell_phone', 'home_phone', 'occupation', 'user_name'] as $field) {
            if (($payload[$field] ?? '') === '') {
                $payload[$field] = '—';
            }
        }

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['name'],
            $payload['last_name'],
            $payload['email'],
            $payload['cell_phone'],
            $payload['home_phone'],
            $payload['occupation'],
            $payload['user_name'],
            $payload['status'],
            $payload['created_at'],
            $payload['deleted_at'],
        ];
    }
}

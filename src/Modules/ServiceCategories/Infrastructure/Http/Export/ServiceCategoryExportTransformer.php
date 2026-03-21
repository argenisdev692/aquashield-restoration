<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ServiceCategoryExportTransformer
{
    #[\NoDiscard]
    public static function forExcel(ServiceCategoryEloquentModel $model): array
    {
        return $model
            |> self::extract(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> self::toRow(...);
    }

    #[\NoDiscard]
    public static function forPdf(ServiceCategoryEloquentModel $model): object
    {
        return $model
            |> self::extract(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> (fn(array $payload): object => (object) $payload);
    }

    private static function extract(ServiceCategoryEloquentModel $model): array
    {
        return [
            'category'   => $model->category,
            'type'       => $model->type,
            'status'     => $model->deleted_at !== null ? 'Deleted' : 'Active',
            'created_at' => $model->created_at?->toIso8601String(),
            'deleted_at' => $model->deleted_at?->toIso8601String(),
        ];
    }

    private static function formatDates(array $payload): array
    {
        $payload['created_at'] = $payload['created_at'] !== null
            ? CarbonImmutable::parse($payload['created_at'])->format('F j, Y')
            : '—';

        $payload['deleted_at'] = $payload['deleted_at'] !== null
            ? CarbonImmutable::parse($payload['deleted_at'])->format('F j, Y')
            : null;

        return $payload;
    }

    private static function sanitize(array $payload): array
    {
        foreach (['category', 'type', 'status', 'created_at'] as $field) {
            $payload[$field] = $payload[$field] ?? '—';
        }

        return $payload;
    }

    private static function toRow(array $payload): array
    {
        return [
            $payload['category'],
            $payload['type']       ?? '—',
            $payload['status'],
            $payload['created_at'],
            $payload['deleted_at'] ?? '—',
        ];
    }
}

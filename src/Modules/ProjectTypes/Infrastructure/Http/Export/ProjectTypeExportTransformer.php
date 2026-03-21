<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

final class ProjectTypeExportTransformer
{
    #[\NoDiscard]
    public static function forExcel(ProjectTypeEloquentModel $model): array
    {
        return $model
            |> self::extract(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> self::toRow(...);
    }

    #[\NoDiscard]
    public static function forPdf(ProjectTypeEloquentModel $model): object
    {
        return $model
            |> self::extract(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> (fn(array $payload): object => (object) $payload);
    }

    private static function extract(ProjectTypeEloquentModel $model): array
    {
        return [
            'title'            => $model->title,
            'description'      => $model->description,
            'status'           => $model->status,
            'service_category' => $model->serviceCategory?->category ?? '—',
            'record_status'    => $model->deleted_at !== null ? 'Deleted' : 'Active',
            'created_at'       => $model->created_at?->toIso8601String(),
            'deleted_at'       => $model->deleted_at?->toIso8601String(),
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
        foreach (['title', 'description', 'status', 'service_category', 'record_status', 'created_at'] as $field) {
            $payload[$field] = $payload[$field] ?? '—';
        }

        return $payload;
    }

    private static function toRow(array $payload): array
    {
        return [
            $payload['title'],
            $payload['description'],
            $payload['service_category'],
            $payload['status'],
            $payload['record_status'],
            $payload['created_at'],
            $payload['deleted_at'] ?? '—',
        ];
    }
}

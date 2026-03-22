<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;

final class PortfolioExportTransformer
{
    #[\NoDiscard]
    public static function forExcel(PortfolioEloquentModel $model): array
    {
        return $model
            |> self::extract(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> self::toRow(...);
    }

    #[\NoDiscard]
    public static function forPdf(PortfolioEloquentModel $model): object
    {
        return $model
            |> self::extract(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> (fn(array $payload): object => (object) $payload);
    }

    private static function extract(PortfolioEloquentModel $model): array
    {
        return [
            'project_type_title'    => $model->projectType?->title,
            'service_category_name' => $model->projectType?->serviceCategory?->category,
            'image_count'           => $model->images_count ?? $model->images()->count(),
            'record_status'         => $model->deleted_at !== null ? 'Deleted' : 'Active',
            'created_at'            => $model->created_at?->toIso8601String(),
            'deleted_at'            => $model->deleted_at?->toIso8601String(),
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
        foreach (['project_type_title', 'service_category_name', 'image_count', 'record_status', 'created_at'] as $field) {
            $payload[$field] = $payload[$field] ?? '—';
        }

        return $payload;
    }

    private static function toRow(array $payload): array
    {
        return [
            $payload['project_type_title'],
            $payload['service_category_name'],
            $payload['image_count'],
            $payload['record_status'],
            $payload['created_at'],
            $payload['deleted_at'] ?? '—',
        ];
    }
}

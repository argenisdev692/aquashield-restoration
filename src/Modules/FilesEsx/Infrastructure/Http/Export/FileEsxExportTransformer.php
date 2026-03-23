<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;

final class FileEsxExportTransformer
{
    #[\NoDiscard('Excel row array must be captured')]
    public static function transformForExcel(FileEsxEloquentModel $file): array
    {
        return $file
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard('PDF row array must be captured')]
    public static function transformForPdf(FileEsxEloquentModel $file): array
    {
        return $file
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitizePayload(...);
    }

    private static function extractPayload(FileEsxEloquentModel $file): array
    {
        $adjusters = $file->relationLoaded('assignedAdjusters')
            ? $file->assignedAdjusters->pluck('name')->implode(', ')
            : '—';

        return [
            'uuid'       => $file->uuid,
            'file_name'  => $file->file_name,
            'file_path'  => $file->file_path,
            'uploader'   => $file->uploader?->name,
            'adjusters'  => $adjusters ?: '—',
            'status'     => $file->deleted_at !== null ? 'Suspended' : 'Active',
            'created_at' => $file->created_at?->toIso8601String(),
            'deleted_at' => $file->deleted_at?->toIso8601String(),
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
        foreach (['uuid', 'file_name', 'file_path', 'uploader', 'adjusters'] as $field) {
            $payload[$field] = ($payload[$field] ?? '') !== '' ? $payload[$field] : '—';
        }

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['uuid'],
            $payload['file_name'],
            $payload['file_path'],
            $payload['uploader'],
            $payload['adjusters'],
            $payload['status'],
            $payload['created_at'],
            $payload['deleted_at'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;

final class DocumentTemplateExportTransformer
{
    #[\NoDiscard('Excel row array must be captured')]
    public static function forExcel(DocumentTemplateEloquentModel $model): array
    {
        return $model
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard('PDF row array must be captured')]
    public static function forPdf(DocumentTemplateEloquentModel $model): array
    {
        return $model
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitize(...);
    }

    private static function extractPayload(DocumentTemplateEloquentModel $model): array
    {
        return [
            'template_name'        => $model->template_name,
            'template_description' => $model->template_description,
            'template_type'        => $model->template_type,
            'uploaded_by_name'     => $model->uploadedByUser?->name,
            'created_at'           => $model->created_at?->toIso8601String(),
        ];
    }

    private static function formatDates(array $payload): array
    {
        $payload['created_at'] = $payload['created_at'] !== null
            ? CarbonImmutable::parse($payload['created_at'])->format('F j, Y')
            : '—';

        return $payload;
    }

    private static function sanitize(array $payload): array
    {
        $fields = [
            'template_name',
            'template_description',
            'template_type',
            'uploaded_by_name',
        ];

        foreach ($fields as $field) {
            if (($payload[$field] ?? null) === null || $payload[$field] === '') {
                $payload[$field] = '—';
            }
        }

        return $payload;
    }

    private static function toExcelRow(array $payload): array
    {
        return [
            $payload['template_name'],
            $payload['template_description'],
            $payload['template_type'],
            $payload['uploaded_by_name'],
            $payload['created_at'],
        ];
    }
}

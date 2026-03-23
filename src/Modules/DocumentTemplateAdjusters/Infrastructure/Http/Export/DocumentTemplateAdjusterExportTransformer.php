<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;

final class DocumentTemplateAdjusterExportTransformer
{
    #[\NoDiscard('Excel row array must be captured')]
    public static function forExcel(DocumentTemplateAdjusterEloquentModel $model): array
    {
        return $model
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard('PDF row array must be captured')]
    public static function forPdf(DocumentTemplateAdjusterEloquentModel $model): array
    {
        return $model
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitize(...);
    }

    private static function extractPayload(DocumentTemplateAdjusterEloquentModel $model): array
    {
        return [
            'template_description_adjuster' => $model->template_description_adjuster,
            'template_type_adjuster'        => $model->template_type_adjuster,
            'template_path_adjuster'        => $model->template_path_adjuster,
            'public_adjuster_name'          => $model->publicAdjuster?->name,
            'uploaded_by_name'              => $model->uploadedByUser?->name,
            'created_at'                    => $model->created_at?->toIso8601String(),
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
            'template_description_adjuster',
            'template_type_adjuster',
            'template_path_adjuster',
            'public_adjuster_name',
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
            $payload['template_description_adjuster'],
            $payload['template_type_adjuster'],
            $payload['public_adjuster_name'],
            $payload['uploaded_by_name'],
            $payload['created_at'],
        ];
    }
}

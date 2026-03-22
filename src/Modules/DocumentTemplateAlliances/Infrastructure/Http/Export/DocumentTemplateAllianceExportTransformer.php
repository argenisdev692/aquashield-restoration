<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Export;

use Carbon\CarbonImmutable;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;

final class DocumentTemplateAllianceExportTransformer
{
    #[\NoDiscard('Excel row array must be captured')]
    public static function forExcel(DocumentTemplateAllianceEloquentModel $model): array
    {
        return $model
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitize(...)
            |> self::toExcelRow(...);
    }

    #[\NoDiscard('PDF row array must be captured')]
    public static function forPdf(DocumentTemplateAllianceEloquentModel $model): array
    {
        return $model
            |> self::extractPayload(...)
            |> self::formatDates(...)
            |> self::sanitize(...);
    }

    private static function extractPayload(DocumentTemplateAllianceEloquentModel $model): array
    {
        return [
            'template_name_alliance'        => $model->template_name_alliance,
            'template_description_alliance' => $model->template_description_alliance,
            'template_type_alliance'        => $model->template_type_alliance,
            'alliance_company_name'         => $model->allianceCompany?->alliance_company_name,
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
            'template_name_alliance',
            'template_description_alliance',
            'template_type_alliance',
            'alliance_company_name',
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
            $payload['template_name_alliance'],
            $payload['template_description_alliance'],
            $payload['template_type_alliance'],
            $payload['alliance_company_name'],
            $payload['uploaded_by_name'],
            $payload['created_at'],
        ];
    }
}

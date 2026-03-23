<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Export;

use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

final class ZoneExportTransformer
{
    #[\NoDiscard("Excel row array must be captured")]
    public static function forExcel(ZoneEloquentModel $model): array
    {
        return [
            $model->zone_name,
            ucfirst($model->zone_type),
            $model->code ?? '—',
            $model->description ?? '—',
            $model->deleted_at === null ? 'Active' : 'Suspended',
            $model->created_at?->format('Y-m-d H:i:s') ?? '—',
            $model->deleted_at?->format('Y-m-d H:i:s') ?? '—',
        ];
    }

    #[\NoDiscard("PDF row object must be captured")]
    public static function forPdf(ZoneEloquentModel $model): object
    {
        return (object) [
            'zone_name'   => $model->zone_name,
            'zone_type'   => ucfirst($model->zone_type),
            'code'        => $model->code ?? '—',
            'description' => $model->description ?? '—',
            'status'      => $model->deleted_at === null ? 'Active' : 'Suspended',
            'created_at'  => $model->created_at?->format('Y-m-d H:i:s') ?? '—',
            'deleted_at'  => $model->deleted_at?->format('Y-m-d H:i:s') ?? '—',
        ];
    }
}

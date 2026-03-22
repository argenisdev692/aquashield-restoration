<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Http\Export;

use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;

final class ClaimStatusExportTransformer
{
    public static function forExcel(ClaimStatusEloquentModel $model): array
    {
        return [
            $model->claim_status_name,
            $model->background_color ?? '—',
            $model->deleted_at === null ? 'Active' : 'Deleted',
            $model->created_at?->format('Y-m-d H:i:s') ?? '—',
            $model->deleted_at?->format('Y-m-d H:i:s') ?? '—',
        ];
    }

    public static function forPdf(ClaimStatusEloquentModel $model): object
    {
        return (object) [
            'claim_status_name' => $model->claim_status_name,
            'background_color'  => $model->background_color ?? '—',
            'status'            => $model->deleted_at === null ? 'Active' : 'Deleted',
            'created_at'        => $model->created_at?->format('Y-m-d H:i:s') ?? '—',
            'deleted_at'        => $model->deleted_at?->format('Y-m-d H:i:s') ?? '—',
        ];
    }
}

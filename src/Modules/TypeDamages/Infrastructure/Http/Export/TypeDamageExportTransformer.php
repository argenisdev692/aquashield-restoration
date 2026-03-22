<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Export;

use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

final class TypeDamageExportTransformer
{
    public static function forExcel(TypeDamageEloquentModel $model): array
    {
        return [
            $model->type_damage_name,
            $model->description ?? '—',
            ucfirst($model->severity),
            $model->deleted_at === null ? 'Active' : 'Deleted',
            $model->created_at?->format('Y-m-d H:i:s') ?? '—',
            $model->deleted_at?->format('Y-m-d H:i:s') ?? '—',
        ];
    }

    public static function forPdf(TypeDamageEloquentModel $model): object
    {
        return (object) [
            'type_damage_name' => $model->type_damage_name,
            'description'      => $model->description ?? '—',
            'severity'         => ucfirst($model->severity),
            'status'           => $model->deleted_at === null ? 'Active' : 'Deleted',
            'created_at'       => $model->created_at?->format('Y-m-d H:i:s') ?? '—',
            'deleted_at'       => $model->deleted_at?->format('Y-m-d H:i:s') ?? '—',
        ];
    }
}

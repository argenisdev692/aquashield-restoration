<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class TypeDamageEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'type_damages';

    protected $fillable = [
        'uuid',
        'type_damage_name',
        'description',
        'severity',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type_damage_name',
                'description',
                'severity',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('type_damages.type_damage');
    }
}

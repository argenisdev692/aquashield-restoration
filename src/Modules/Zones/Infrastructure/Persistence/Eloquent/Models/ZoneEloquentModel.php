<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class ZoneEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'zones';

    protected $fillable = [
        'uuid',
        'zone_name',
        'zone_type',
        'code',
        'description',
        'user_id',
    ];

    /**
     * @return BelongsTo<UserEloquentModel, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'zone_name',
                'zone_type',
                'code',
                'description',
                'user_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('zones.zone');
    }
}

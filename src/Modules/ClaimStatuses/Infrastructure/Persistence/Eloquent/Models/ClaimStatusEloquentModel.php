<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

final class ClaimStatusEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'claim_status';

    protected $fillable = [
        'uuid',
        'claim_status_name',
        'background_color',
    ];

    /** @return HasMany<ClaimEloquentModel, $this> */
    public function claims(): HasMany
    {
        return $this->hasMany(ClaimEloquentModel::class, 'claim_status');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'claim_status_name',
                'background_color',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('claim_statuses.claim_status');
    }
}

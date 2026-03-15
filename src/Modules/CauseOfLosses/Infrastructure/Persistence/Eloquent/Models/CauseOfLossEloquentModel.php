<?php

declare(strict_types=1);

namespace Src\Modules\CauseOfLosses\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class CauseOfLossEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'cause_of_losses';

    protected $fillable = [
        'uuid',
        'cause_loss_name',
        'description',
        'severity',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'cause_loss_name',
                'description',
                'severity',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('cause_of_losses.cause_of_loss');
    }
}

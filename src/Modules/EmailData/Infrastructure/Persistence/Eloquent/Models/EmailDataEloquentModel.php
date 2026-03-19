<?php

declare(strict_types=1);

namespace Modules\EmailData\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class EmailDataEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'email_data';

    protected $fillable = [
        'uuid',
        'description',
        'email',
        'phone',
        'type',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'description',
                'email',
                'phone',
                'type',
                'user_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }
}

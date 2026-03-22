<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class CallHistoryEloquentModel extends Model
{
    use HasFactory;
    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'call_histories';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'call_id',
        'agent_id',
        'agent_name',
        'from_number',
        'to_number',
        'direction',
        'call_status',
        'start_timestamp',
        'end_timestamp',
        'duration_ms',
        'transcript',
        'recording_url',
        'call_analysis',
        'disconnection_reason',
        'metadata',
        'call_type',
    ];

    protected $casts = [
        'start_timestamp' => 'datetime',
        'end_timestamp' => 'datetime',
        'duration_ms' => 'integer',
        'call_analysis' => 'array',
        'metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'call_id',
                'agent_name',
                'from_number',
                'to_number',
                'direction',
                'call_status',
                'call_type',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

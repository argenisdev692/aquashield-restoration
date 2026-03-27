<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class CampaignEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'ai_campaigns';

    protected $fillable = [
        'uuid',
        'title',
        'niche',
        'platform',
        'caption',
        'hashtags',
        'call_to_action',
        'image_path',
        'image_url',
        'status',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}

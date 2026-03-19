<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class AllianceCompanyEloquentModel extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'alliance_companies';

    protected $fillable = [
        'uuid',
        'alliance_company_name',
        'address',
        'phone',
        'email',
        'website',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('alliance_company')
            ->logOnly([
                'alliance_company_name',
                'address',
                'phone',
                'email',
                'website',
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

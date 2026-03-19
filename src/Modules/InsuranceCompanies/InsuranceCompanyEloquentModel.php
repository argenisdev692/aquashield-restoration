<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/**
 * InsuranceCompanyEloquentModel
 *
 * @internal — Infrastructure only. Use InsuranceCompanyRepositoryPort.
 */
final class InsuranceCompanyEloquentModel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'insurance_companies';

    protected $fillable = [
        'uuid',
        'insurance_company_name',
        'address',
        'phone',
        'email',
        'website',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['insurance_company_name', 'address', 'phone', 'email', 'website', 'user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('crm.insurance_companies');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query->when($from, fn(Builder $q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn(Builder $q) => $q->whereDate('created_at', '<=', $to));
    }
}

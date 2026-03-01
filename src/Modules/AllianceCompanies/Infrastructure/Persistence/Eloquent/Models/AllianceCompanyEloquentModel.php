<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/**
 * AllianceCompanyEloquentModel
 * 
 * @internal â€” Infrastructure only. Use AllianceCompanyRepositoryPort.
 */
class AllianceCompanyEloquentModel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    public function scopeInDateRange($query, ?string $from, ?string $to): void
    {
        $query->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));
    }
}

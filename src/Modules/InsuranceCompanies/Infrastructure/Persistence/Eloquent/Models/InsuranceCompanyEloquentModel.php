<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * InsuranceCompanyEloquentModel
 *
 * @internal — Infrastructure only. Use InsuranceCompanyRepositoryPort.
 */
final class InsuranceCompanyEloquentModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'insurance_companies';

    protected $fillable = [
        'uuid',
        'insurance_company_name',
        'address',
        'address_2',
        'phone',
        'email',
        'website',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['insurance_company_name', 'address', 'address_2', 'phone', 'email', 'website', 'user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('crm.insurance_companies');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, static function (Builder $builder, string $term): void {
            $builder->where(static function (Builder $nested) use ($term): void {
                $nested->where('insurance_company_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%")
                    ->orWhere('address_2', 'like', "%{$term}%")
                    ->orWhere('website', 'like', "%{$term}%");
            });
        });
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query->when($from, fn (Builder $builder): Builder => $builder->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $builder): Builder => $builder->whereDate('created_at', '<=', $to));
    }
}

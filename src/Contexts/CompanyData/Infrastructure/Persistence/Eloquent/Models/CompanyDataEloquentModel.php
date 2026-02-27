<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Infrastructure\Persistence\Eloquent\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * CompanyDataEloquentModel
 * 
 * @internal — Infrastructure only. Use CompanyDataRepositoryPort.
 */
final class CompanyDataEloquentModel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'company_data';

    protected $fillable = [
        'uuid',
        'name',
        'company_name',
        'signature_path',
        'email',
        'phone',
        'address',
        'website',
        'user_id',
        'latitude',
        'longitude',
        'facebook_link',
        'instagram_link',
        'linkedin_link',
        'twitter_link'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "CompanyData ha sido {$eventName}");
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param Builder $query
     * @param string|null $from
     * @param string|null $to
     * @param string $column
     * @return Builder
     */
    public function scopeInDateRange(
        Builder $query,
        ?string $from,
        ?string $to,
        string $column = 'created_at'
    ): Builder {
        return $query
            ->when($from, fn($q) => $q->whereDate($column, '>=', $from))
            ->when($to, fn($q) => $q->whereDate($column, '<=', $to));
    }
}

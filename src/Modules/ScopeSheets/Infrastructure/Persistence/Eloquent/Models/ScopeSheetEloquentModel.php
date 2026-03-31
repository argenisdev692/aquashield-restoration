<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

/** @internal — Infrastructure only. Use ScopeSheetRepositoryPort. */
final class ScopeSheetEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'scope_sheets';

    protected $fillable = [
        'uuid',
        'claim_id',
        'scope_sheet_description',
        'generated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'claim_id',
                'scope_sheet_description',
                'generated_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('scope_sheets.scope_sheet');
    }

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'generated_by');
    }

    /** @return HasMany<ScopeSheetPresentationEloquentModel, $this> */
    public function presentations(): HasMany
    {
        return $this->hasMany(ScopeSheetPresentationEloquentModel::class, 'scope_sheet_id')
            ->orderBy('photo_order');
    }

    /** @return HasMany<ScopeSheetZoneEloquentModel, $this> */
    public function zones(): HasMany
    {
        return $this->hasMany(ScopeSheetZoneEloquentModel::class, 'scope_sheet_id')
            ->orderBy('zone_order');
    }

    /** @return HasOne<ScopeSheetExportEloquentModel, $this> */
    public function exportRecord(): HasOne
    {
        return $this->hasOne(ScopeSheetExportEloquentModel::class, 'scope_sheet_id')
            ->latest();
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, static function (Builder $builder, string $term): void {
            $builder->where(static function (Builder $nested) use ($term): void {
                $nested->where('scope_sheet_description', 'like', "%{$term}%")
                    ->orWhereHas('claim', fn (Builder $q) => $q->where('claim_number', 'like', "%{$term}%")
                        ->orWhere('claim_internal_id', 'like', "%{$term}%"));
            });
        });
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $b): Builder => $b->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $b): Builder => $b->whereDate('created_at', '<=', $to));
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        return $query
            ->when($status === 'deleted', fn (Builder $b): Builder => $b->onlyTrashed())
            ->when($status === 'active', fn (Builder $b): Builder => $b->whereNull('deleted_at'));
    }
}

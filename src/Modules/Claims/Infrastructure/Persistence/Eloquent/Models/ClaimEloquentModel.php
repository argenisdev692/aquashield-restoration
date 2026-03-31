<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Eloquent\Models\CauseOfLossEloquentModel;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

/** @internal — Infrastructure only. Use ClaimRepositoryPort. */
final class ClaimEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'claims';

    protected $fillable = [
        'uuid',
        'property_id',
        'signature_path_id',
        'type_damage_id',
        'user_id_ref_by',
        'claim_status',
        'claim_number',
        'claim_internal_id',
        'policy_number',
        'date_of_loss',
        'description_of_loss',
        'number_of_floors',
        'claim_date',
        'work_date',
        'damage_description',
        'scope_of_work',
        'customer_reviewed',
    ];

    protected $casts = [
        'customer_reviewed' => 'boolean',
        'number_of_floors'  => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'claim_number',
                'claim_internal_id',
                'policy_number',
                'claim_status',
                'type_damage_id',
                'date_of_loss',
                'damage_description',
                'scope_of_work',
                'customer_reviewed',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('claims.claim');
    }

    /** @return BelongsTo<PropertyEloquentModel, $this> */
    public function property(): BelongsTo
    {
        return $this->belongsTo(PropertyEloquentModel::class, 'property_id');
    }

    /** @return BelongsTo<ClaimStatusEloquentModel, $this> */
    public function claimStatus(): BelongsTo
    {
        return $this->belongsTo(ClaimStatusEloquentModel::class, 'claim_status');
    }

    /** @return BelongsTo<TypeDamageEloquentModel, $this> */
    public function typeDamage(): BelongsTo
    {
        return $this->belongsTo(TypeDamageEloquentModel::class, 'type_damage_id');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function referredByUser(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id_ref_by');
    }

    /** @return HasOne<InsuranceCompanyAssignmentEloquentModel, $this> */
    public function insuranceCompanyAssignment(): HasOne
    {
        return $this->hasOne(InsuranceCompanyAssignmentEloquentModel::class, 'claim_id');
    }

    /** @return HasOne<InsuranceAdjusterAssignmentEloquentModel, $this> */
    public function insuranceAdjusterAssignment(): HasOne
    {
        return $this->hasOne(InsuranceAdjusterAssignmentEloquentModel::class, 'claim_id');
    }

    /** @return HasOne<PublicCompanyAssignmentEloquentModel, $this> */
    public function publicCompanyAssignment(): HasOne
    {
        return $this->hasOne(PublicCompanyAssignmentEloquentModel::class, 'claim_id');
    }

    /** @return HasOne<PublicAdjusterAssignmentEloquentModel, $this> */
    public function publicAdjusterAssignment(): HasOne
    {
        return $this->hasOne(PublicAdjusterAssignmentEloquentModel::class, 'claim_id');
    }

    /** @return HasOne<ClaimAllianceEloquentModel, $this> */
    public function claimAlliance(): HasOne
    {
        return $this->hasOne(ClaimAllianceEloquentModel::class, 'claim_id');
    }

    /** @return HasMany<ClaimAgreementEloquentModel, $this> */
    public function claimAgreements(): HasMany
    {
        return $this->hasMany(ClaimAgreementEloquentModel::class, 'claim_id');
    }

    /** @return HasMany<ClaimAgreementFullEloquentModel, $this> */
    public function claimAgreementFulls(): HasMany
    {
        return $this->hasMany(ClaimAgreementFullEloquentModel::class, 'claim_id');
    }

    /** @return HasMany<ClaimAgreementAllianceEloquentModel, $this> */
    public function claimAgreementAlliances(): HasMany
    {
        return $this->hasMany(ClaimAgreementAllianceEloquentModel::class, 'claim_id');
    }

    /** @return HasMany<ScopeSheetEloquentModel, $this> */
    public function scopeSheets(): HasMany
    {
        return $this->hasMany(ScopeSheetEloquentModel::class, 'claim_id');
    }

    /** @return BelongsToMany<CauseOfLossEloquentModel, $this> */
    public function causesOfLoss(): BelongsToMany
    {
        return $this->belongsToMany(
            CauseOfLossEloquentModel::class,
            'claim_cause_of_loss',
            'claim_id',
            'cause_of_loss_id',
        )->withTimestamps();
    }

    /** @return BelongsToMany<ServiceRequestEloquentModel, $this> */
    public function serviceRequests(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceRequestEloquentModel::class,
            'claim_services',
            'claim_id',
            'service_request_id',
        )->withTimestamps();
    }

    /** @return BelongsToMany<AllianceCompanyEloquentModel, $this> */
    public function allianceCompanies(): BelongsToMany
    {
        return $this->belongsToMany(
            AllianceCompanyEloquentModel::class,
            'claim_alliances',
            'claim_id',
            'alliance_company_id',
        )->withPivot('assignment_date')->withTimestamps();
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, static function (Builder $builder, string $term): void {
            $builder->where(static function (Builder $nested) use ($term): void {
                $nested->where('claim_number', 'like', "%{$term}%")
                    ->orWhere('claim_internal_id', 'like', "%{$term}%")
                    ->orWhere('policy_number', 'like', "%{$term}%")
                    ->orWhere('description_of_loss', 'like', "%{$term}%");
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
        return $query->when($status === 'deleted', fn (Builder $b): Builder => $b->onlyTrashed())
                     ->when($status === 'active', fn (Builder $b): Builder => $b->whereNull('deleted_at'));
    }
}

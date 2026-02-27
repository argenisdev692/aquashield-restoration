<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InsuranceCompany extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'uuid',
        'insurance_company_name',
        'address',
        'phone',
        'email',
        'website',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns the insurance company.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the insurance adjusters for the insurance company.
     */
    public function insuranceAdjusters(): HasMany
    {
        return $this->hasMany(InsuranceAdjuster::class);
    }

    /**
     * Get the insurance company assignments for the insurance company.
     */
    public function insuranceCompanyAssignments(): HasMany
    {
        return $this->hasMany(InsuranceCompanyAssignment::class);
    }

    /**
     * Get the alliance companies for the insurance company.
     */
    public function allianceCompanies(): HasMany
    {
        return $this->hasMany(AllianceCompany::class);
    }

    /**
     * Check if the insurance company is active (not soft deleted).
     */
    public function isActive(): bool
    {
        return !$this->trashed();
    }

    /**
     * Get the status label based on soft delete status.
     */
    public function getStatusLabel(): string
    {
        return match($this->trashed()) {
            false => 'Active',
            true => 'Inactive',
        };
    }

    /**
     * Get the status color based on soft delete status.
     */
    public function getStatusColor(): string
    {
        return match($this->trashed()) {
            false => 'green',
            true => 'red',
        };
    }

    /**
     * Get formatted phone number.
     */
    public function getFormattedPhone(): string
    {
        return $this->phone ? preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '($1) $2-$3', $this->phone) : '';
    }

    /**
     * Get display name for the insurance company.
     */
    public function getDisplayName(): string
    {
        return $this->insurance_company_name ?? 'Unnamed Company';
    }
}

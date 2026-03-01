<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Support\{Str, Facades\Log};


class W9Form extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'business_name',
        'is_individual_sole_proprietor',
        'is_corporation',
        'is_partnership',
        'is_limited_liability_company',
        'is_exempt_payee',
        'is_other',
        'llc_tax_classification',
        'address',
        'address_2',
        'city',
        'state',
        'zip_code',
        'country',
        'latitude',
        'longitude',
        'requester_name_address',
        'account_numbers',
        'social_security_number',
        'employer_identification_number',
        'certification_signed',
        'signature_date',
        'status',
        'notes',
        'document_path'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_individual_sole_proprietor' => 'boolean',
        'is_corporation' => 'boolean',
        'is_partnership' => 'boolean',
        'is_limited_liability_company' => 'boolean',
        'is_exempt_payee' => 'boolean',
        'is_other' => 'boolean',
        'certification_signed' => 'boolean',
        'signature_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'social_security_number',
        'employer_identification_number',
    ];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    protected $table = 'w9_forms';

    /**
     * Relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get tax classification display text
     *
     * @return string
     */
    public function getTaxClassificationAttribute(): string
    {
        $classifications = [];
        
        if ($this->is_individual_sole_proprietor) $classifications[] = 'Individual/Sole proprietor';
        if ($this->is_corporation) $classifications[] = 'Corporation';
        if ($this->is_partnership) $classifications[] = 'Partnership';
        if ($this->is_limited_liability_company) {
            $classifications[] = 'Limited Liability Company';
            if ($this->llc_tax_classification) {
                $classifications[] .= " ({$this->llc_tax_classification})";
            }
        }
        if ($this->is_exempt_payee) $classifications[] = 'Exempt payee';
        if ($this->is_other) $classifications[] = 'Other';

        return implode(', ', $classifications);
    }

    /**
     * Get formatted address
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->city}, {$this->state} {$this->zip_code}";
    }

    /**
     * Scope a query to only include forms with specific status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if form is complete
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if form has tax identification number
     *
     * @return bool
     */
    public function hasTaxIdentification(): bool
    {
        return !empty($this->social_security_number) || 
               !empty($this->employer_identification_number);
    }

    /**
     * Get masked SSN for display
     *
     * @return string|null
     */
    public function getMaskedSsnAttribute(): ?string
    {
        if (!$this->social_security_number) return null;
        return 'XXX-XX-' . substr($this->social_security_number, -4);
    }

    /**
     * Get masked EIN for display
     *
     * @return string|null
     */
    public function getMaskedEinAttribute(): ?string
    {
        if (!$this->employer_identification_number) return null;
        return 'XX-XXX' . substr($this->employer_identification_number, -4);
    }

    /**
     * Check if form is signed
     *
     * @return bool
     */
    public function isSigned(): bool
    {
        return $this->certification_signed && !is_null($this->signature_date);
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
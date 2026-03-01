<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'claim_number',
        'property_address',
        'damage_type',
        'estimated_cost',
        'insurance_company',
        'policy_number',
        'description',
        'status',
        'priority',
        'contact_name',
        'contact_phone',
        'contact_email',
        'scheduled_inspection_date',
        'inspection_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'scheduled_inspection_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    // Accessors
    public function getFormattedEstimatedCostAttribute(): string
    {
        return '$' . number_format($this->estimated_cost, 2);
    }

    public function getStatusDisplayAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getPriorityDisplayAttribute(): string
    {
        return ucfirst($this->priority);
    }
}
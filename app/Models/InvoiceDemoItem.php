<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceDemoItem extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'invoice_demo_id',
        'service_name',
        'description',
        'quantity',
        'rate',
        'amount',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the invoice that owns the item.
     */
    public function invoiceDemo(): BelongsTo
    {
        return $this->belongsTo(InvoiceDemo::class);
    }

    /**
     * Calculate the amount based on quantity and rate.
     */
    public function calculateAmount(): void
    {
        $this->amount = $this->quantity * $this->rate;
        $this->save();
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get formatted amount for display.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted rate for display.
     */
    public function getFormattedRateAttribute(): string
    {
        return '$' . number_format($this->rate, 2);
    }

    /**
     * Scope for filtering by service name.
     */
    public function scopeByService($query, $serviceName)
    {
        return $query->where('service_name', 'like', "%{$serviceName}%");
    }

    /**
     * Scope for expensive items (rate above threshold).
     */
    public function scopeExpensive($query, $threshold = 100)
    {
        return $query->where('rate', '>', $threshold);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate amount when creating/updating
        static::saving(function ($item) {
            $item->amount = $item->quantity * $item->rate;
        });

        // Update invoice totals when item is saved or deleted
        static::saved(function ($item) {
            $item->invoiceDemo->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->invoiceDemo->calculateTotals();
        });
    }
} 
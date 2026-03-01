<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceDemo extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'invoice_number',
        'invoice_date',
        'bill_to_name',
        'bill_to_email',
        'bill_to_address',
        'bill_to_phone',
        'subtotal',
        'tax_amount',
        'balance_due',
        'pdf_url',
        'claim_number',
        'policy_number',
        'insurance_company',
        'date_of_loss',
        'date_received',
        'date_inspected',
        'date_entered',
        'price_list_code',
        'type_of_loss',
        'notes',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'date_of_loss' => 'date',
            'date_received' => 'datetime',
            'date_inspected' => 'datetime',
            'date_entered' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    /**
     * Get the items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceDemoItem::class)->orderBy('sort_order');
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
     * Calculate and update the invoice totals based on items.
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('amount');
        $this->update([
            'subtotal' => $subtotal,
            'balance_due' => $subtotal + $this->tax_amount,
        ]);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Get formatted balance due for display.
     */
    public function getFormattedBalanceDueAttribute(): string
    {
        return '$' . number_format($this->balance_due, 2);
    }

    /**
     * Get formatted subtotal for display.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Get formatted tax amount for display.
     */
    public function getFormattedTaxAmountAttribute(): string
    {
        return '$' . number_format($this->tax_amount, 2);
    }

    /**
     * Get status display with proper formatting.
     */
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'paid' => 'green',
            'cancelled' => 'red',
            'print_pdf' => 'purple',
            default => 'gray'
        };
    }

    /**
     * Set invoice number with automatic formatting.
     */
    public function setInvoiceNumberAttribute($value)
    {
        // Ensure invoice number follows VG-XXXX format
        if (!str_starts_with($value, 'VG-')) {
            $this->attributes['invoice_number'] = 'VG-' . str_pad($value, 4, '0', STR_PAD_LEFT);
        } else {
            $this->attributes['invoice_number'] = $value;
        }
    }

    /**
     * Generate next available invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = static::latest('id')->first();
        $lastNumber = $lastInvoice ? (int) str_replace('VG-', '', $lastInvoice->invoice_number) : 0;
        $nextNumber = $lastNumber + 1;
        
        return 'VG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if invoice is overdue (past due date).
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'paid') {
            return false;
        }
        
        // Assume 30 days payment terms
        $dueDate = $this->invoice_date->addDays(30);
        return now()->isAfter($dueDate);
    }

    /**
     * Get days overdue.
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        $dueDate = $this->invoice_date->addDays(30);
        return now()->diffInDays($dueDate);
    }

    /**
     * Scope for overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
                    ->whereDate('invoice_date', '<', now()->subDays(30));
    }

    /**
     * Scope for paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
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
        'bill_to_address',
        'bill_to_phone',
        'bill_to_email',
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
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Calculate the total amount of the invoice.
     */
    public function calculateTotal(): void
    {
        $this->subtotal = $this->items()->sum('amount');
        $this->balance_due = $this->subtotal + $this->tax_amount;
        $this->save();
    }
}
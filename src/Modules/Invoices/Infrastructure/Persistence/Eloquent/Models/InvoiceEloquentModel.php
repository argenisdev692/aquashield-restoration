<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

/** @internal — Infrastructure only. Use InvoiceRepositoryPort. */
final class InvoiceEloquentModel extends Model
{
    use SoftDeletes;
    use HasUuids;
    use LogsActivity;

    protected $table = 'invoices';

    protected $fillable = [
        'uuid',
        'user_id',
        'claim_id',
        'invoice_number',
        'invoice_date',
        'bill_to_name',
        'bill_to_address',
        'bill_to_phone',
        'bill_to_email',
        'subtotal',
        'tax_amount',
        'balance_due',
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
        'pdf_url',
    ];

    protected $casts = [
        'invoice_date'   => 'date',
        'date_of_loss'   => 'date',
        'date_received'  => 'datetime',
        'date_inspected' => 'datetime',
        'date_entered'   => 'datetime',
        'subtotal'       => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'balance_due'    => 'decimal:2',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'invoice_number',
                'status',
                'bill_to_name',
                'subtotal',
                'tax_amount',
                'balance_due',
                'claim_number',
                'claim_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('invoices.invoice');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return HasMany<InvoiceItemEloquentModel, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItemEloquentModel::class, 'invoice_id')->orderBy('sort_order');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, static function (Builder $builder, string $term): void {
            $builder->where(static function (Builder $nested) use ($term): void {
                $nested->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('bill_to_name', 'like', "%{$term}%")
                    ->orWhere('bill_to_email', 'like', "%{$term}%")
                    ->orWhere('claim_number', 'like', "%{$term}%")
                    ->orWhere('insurance_company', 'like', "%{$term}%");
            });
        });
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $b): Builder => $b->whereDate('invoice_date', '>=', $from))
            ->when($to, fn (Builder $b): Builder => $b->whereDate('invoice_date', '<=', $to));
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        return $query
            ->when($status === 'deleted', fn (Builder $b): Builder => $b->onlyTrashed())
            ->when($status === 'active', fn (Builder $b): Builder => $b->whereNull('deleted_at'));
    }

    public function scopeByInvoiceStatus(Builder $query, ?string $invoiceStatus): Builder
    {
        return $query->when($invoiceStatus, fn (Builder $b): Builder => $b->where('status', $invoiceStatus));
    }
}

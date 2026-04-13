<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** @internal — Infrastructure only. Managed through InvoiceEloquentModel. */
final class InvoiceItemEloquentModel extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'invoice_items';

    protected $fillable = [
        'uuid',
        'invoice_id',
        'service_name',
        'description',
        'quantity',
        'rate',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'rate'       => 'decimal:2',
        'amount'     => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /** @return BelongsTo<InvoiceEloquentModel, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceEloquentModel::class, 'invoice_id');
    }
}

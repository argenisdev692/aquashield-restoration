<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;

final class CustomerPropertyEloquentModel extends Pivot
{
    protected $table = 'customer_properties';

    protected $fillable = [
        'property_id',
        'customer_id',
        'role',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(PropertyEloquentModel::class, 'property_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerEloquentModel::class, 'customer_id');
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCoOwner(): bool
    {
        return $this->role === 'co-owner';
    }
}

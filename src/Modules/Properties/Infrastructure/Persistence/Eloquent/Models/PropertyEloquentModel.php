<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;

final class PropertyEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'properties';

    protected $fillable = [
        'uuid',
        'property_address',
        'property_address_2',
        'property_state',
        'property_city',
        'property_postal_code',
        'property_country',
        'property_latitude',
        'property_longitude',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'property_address',
                'property_address_2',
                'property_state',
                'property_city',
                'property_postal_code',
                'property_country',
                'property_latitude',
                'property_longitude',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('properties.property');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomerEloquentModel::class,
            'customer_properties',
            'property_id',
            'customer_id',
        )
            ->using(CustomerPropertyEloquentModel::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /** @return HasMany<ClaimEloquentModel, $this> */
    public function claims(): HasMany
    {
        return $this->hasMany(ClaimEloquentModel::class, 'property_id');
    }
}

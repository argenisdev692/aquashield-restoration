<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\CustomerPropertyEloquentModel;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class CustomerEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'customers';

    protected $fillable = [
        'uuid',
        'name',
        'last_name',
        'email',
        'cell_phone',
        'home_phone',
        'occupation',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'last_name', 'email', 'cell_phone', 'home_phone', 'occupation', 'user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('customers.customer');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(
            PropertyEloquentModel::class,
            'customer_properties',
            'customer_id',
            'property_id',
        )
            ->using(CustomerPropertyEloquentModel::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\Products\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;

/** @internal */
final class ProductEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'product_category_id',
        'product_name',
        'product_description',
        'price',
        'unit',
        'order_position',
    ];

    public function categoryProduct(): BelongsTo
    {
        return $this->belongsTo(CategoryProductEloquentModel::class, 'product_category_id')
            ->withTrashed();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'product_name',
                'product_description',
                'price',
                'unit',
                'order_position'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('products.product');
    }
}

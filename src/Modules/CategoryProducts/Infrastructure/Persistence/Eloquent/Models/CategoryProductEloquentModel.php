<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\Products\Infrastructure\Persistence\Eloquent\Models\ProductEloquentModel;

final class CategoryProductEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'category_products';

    protected $fillable = [
        'uuid',
        'category_product_name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(ProductEloquentModel::class, 'product_category_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'category_product_name',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('category_products.category_product');
    }
}

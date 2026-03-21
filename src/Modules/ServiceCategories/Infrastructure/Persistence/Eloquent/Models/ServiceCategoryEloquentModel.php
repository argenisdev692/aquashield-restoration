<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class ServiceCategoryEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'service_categories';

    protected $fillable = [
        'uuid',
        'category',
        'type',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('service_categories.service_category');
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

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

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    /** @return HasMany<ProjectTypeEloquentModel, $this> */
    public function projectTypes(): HasMany
    {
        return $this->hasMany(ProjectTypeEloquentModel::class, 'service_category_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('service_categories.service_category');
    }
}

<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models\PortfolioEloquentModel;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

final class ProjectTypeEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'project_types';

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'status',
        'service_category_id',
        'user_id',
    ];

    /** @return BelongsTo<ServiceCategoryEloquentModel, $this> */
    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategoryEloquentModel::class, 'service_category_id');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    /** @return HasMany<PortfolioEloquentModel, $this> */
    public function portfolios(): HasMany
    {
        return $this->hasMany(PortfolioEloquentModel::class, 'project_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'status', 'service_category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('project_types.project_type');
    }
}

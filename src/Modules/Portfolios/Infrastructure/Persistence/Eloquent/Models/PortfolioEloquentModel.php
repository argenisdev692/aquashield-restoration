<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;

/** @internal */
final class PortfolioEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'portfolios';

    protected $fillable = [
        'uuid',
        'project_type_id',
    ];

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectTypeEloquentModel::class, 'project_type_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImageEloquentModel::class, 'portfolio_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['uuid', 'project_type_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('portfolios.portfolio');
    }
}

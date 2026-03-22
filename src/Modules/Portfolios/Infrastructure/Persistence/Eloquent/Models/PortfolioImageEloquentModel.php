<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** @internal */
final class PortfolioImageEloquentModel extends Model
{
    use LogsActivity;

    protected $table = 'portfolio_images';

    protected $fillable = [
        'uuid',
        'portfolio_id',
        'path',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(PortfolioEloquentModel::class, 'portfolio_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['path', 'order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('portfolios.portfolio_image');
    }
}

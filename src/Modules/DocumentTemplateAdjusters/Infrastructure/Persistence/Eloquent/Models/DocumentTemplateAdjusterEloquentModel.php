<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/** @internal */
final class DocumentTemplateAdjusterEloquentModel extends Model
{
    use LogsActivity;

    protected $table = 'document_template_adjusters';

    protected $fillable = [
        'uuid',
        'template_description_adjuster',
        'template_type_adjuster',
        'template_path_adjuster',
        'public_adjuster_id',
        'uploaded_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('document_template_adjuster')
            ->logOnly([
                'template_description_adjuster',
                'template_type_adjuster',
                'template_path_adjuster',
                'public_adjuster_id',
                'uploaded_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<UserEloquentModel, $this>
     */
    public function publicAdjuster(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'public_adjuster_id');
    }

    /**
     * @return BelongsTo<UserEloquentModel, $this>
     */
    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'uploaded_by');
    }
}

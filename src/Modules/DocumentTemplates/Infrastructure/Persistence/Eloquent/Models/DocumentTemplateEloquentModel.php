<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class DocumentTemplateEloquentModel extends Model
{
    use LogsActivity;

    protected $table = 'document_templates';

    protected $fillable = [
        'uuid',
        'template_name',
        'template_description',
        'template_type',
        'template_path',
        'uploaded_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('document_template')
            ->logOnly([
                'template_name',
                'template_description',
                'template_type',
                'template_path',
                'uploaded_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'uploaded_by');
    }
}

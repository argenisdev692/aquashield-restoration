<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class DocumentTemplateAllianceEloquentModel extends Model
{
    use LogsActivity;

    protected $table = 'document_template_alliances';

    protected $fillable = [
        'uuid',
        'template_name_alliance',
        'template_description_alliance',
        'template_type_alliance',
        'template_path_alliance',
        'alliance_company_id',
        'uploaded_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('document_template_alliance')
            ->logOnly([
                'template_name_alliance',
                'template_description_alliance',
                'template_type_alliance',
                'template_path_alliance',
                'alliance_company_id',
                'uploaded_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function allianceCompany(): BelongsTo
    {
        return $this->belongsTo(AllianceCompanyEloquentModel::class, 'alliance_company_id');
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'uploaded_by');
    }
}

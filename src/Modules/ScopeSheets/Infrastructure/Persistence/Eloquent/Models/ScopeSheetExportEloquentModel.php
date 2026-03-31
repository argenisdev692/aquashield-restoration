<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/** @internal */
final class ScopeSheetExportEloquentModel extends Model
{
    protected $table = 'scope_sheet_exports';

    protected $fillable = [
        'uuid',
        'scope_sheet_id',
        'full_pdf_path',
        'generated_by',
    ];

    /** @return BelongsTo<ScopeSheetEloquentModel, $this> */
    public function scopeSheet(): BelongsTo
    {
        return $this->belongsTo(ScopeSheetEloquentModel::class, 'scope_sheet_id');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'generated_by');
    }
}

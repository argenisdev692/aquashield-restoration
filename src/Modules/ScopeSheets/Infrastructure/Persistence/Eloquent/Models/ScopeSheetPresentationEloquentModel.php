<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @internal */
final class ScopeSheetPresentationEloquentModel extends Model
{
    protected $table = 'scope_sheet_presentations';

    protected $fillable = [
        'uuid',
        'scope_sheet_id',
        'photo_type',
        'photo_path',
        'photo_order',
    ];

    protected $casts = [
        'photo_order' => 'integer',
    ];

    /** @return BelongsTo<ScopeSheetEloquentModel, $this> */
    public function scopeSheet(): BelongsTo
    {
        return $this->belongsTo(ScopeSheetEloquentModel::class, 'scope_sheet_id');
    }
}

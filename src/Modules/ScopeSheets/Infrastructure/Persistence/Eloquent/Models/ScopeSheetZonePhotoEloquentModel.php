<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @internal */
final class ScopeSheetZonePhotoEloquentModel extends Model
{
    protected $table = 'scope_sheet_zone_photos';

    protected $fillable = [
        'uuid',
        'scope_sheet_zone_id',
        'photo_path',
        'photo_order',
    ];

    protected $casts = [
        'photo_order' => 'integer',
    ];

    /** @return BelongsTo<ScopeSheetZoneEloquentModel, $this> */
    public function scopeSheetZone(): BelongsTo
    {
        return $this->belongsTo(ScopeSheetZoneEloquentModel::class, 'scope_sheet_zone_id');
    }
}

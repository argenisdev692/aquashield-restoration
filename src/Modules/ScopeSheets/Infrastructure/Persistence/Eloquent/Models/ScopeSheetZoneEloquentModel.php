<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

/** @internal */
final class ScopeSheetZoneEloquentModel extends Model
{
    protected $table = 'scope_sheet_zones';

    protected $fillable = [
        'uuid',
        'scope_sheet_id',
        'zone_id',
        'zone_order',
        'zone_notes',
    ];

    protected $casts = [
        'zone_order' => 'integer',
    ];

    /** @return BelongsTo<ScopeSheetEloquentModel, $this> */
    public function scopeSheet(): BelongsTo
    {
        return $this->belongsTo(ScopeSheetEloquentModel::class, 'scope_sheet_id');
    }

    /** @return BelongsTo<ZoneEloquentModel, $this> */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ZoneEloquentModel::class, 'zone_id');
    }

    /** @return HasMany<ScopeSheetZonePhotoEloquentModel, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(ScopeSheetZonePhotoEloquentModel::class, 'scope_sheet_zone_id')
            ->orderBy('photo_order');
    }
}

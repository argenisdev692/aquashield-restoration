<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/**
 * FileAssignmentEloquentModel
 *
 * @internal — Infrastructure only.
 */
final class FileAssignmentEloquentModel extends Model
{
    protected $table = 'file_assignment_esxes';

    protected $fillable = [
        'file_id',
        'public_adjuster_id',
        'assigned_by',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEsxEloquentModel::class, 'file_id');
    }

    public function publicAdjuster(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'public_adjuster_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'assigned_by');
    }
}

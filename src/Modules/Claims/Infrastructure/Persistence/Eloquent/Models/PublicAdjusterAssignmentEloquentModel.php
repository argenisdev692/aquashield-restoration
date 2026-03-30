<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/** @internal */
final class PublicAdjusterAssignmentEloquentModel extends Model
{
    protected $table = 'public_adjuster_assignments';

    protected $fillable = [
        'claim_id',
        'public_adjuster_id',
        'assignment_date',
    ];

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function publicAdjuster(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'public_adjuster_id');
    }
}

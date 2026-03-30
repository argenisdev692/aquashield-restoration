<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/** @internal */
final class InsuranceAdjusterAssignmentEloquentModel extends Model
{
    protected $table = 'insurance_adjuster_assignments';

    protected $fillable = [
        'claim_id',
        'insurance_adjuster_id',
        'assignment_date',
    ];

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function insuranceAdjuster(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'insurance_adjuster_id');
    }
}

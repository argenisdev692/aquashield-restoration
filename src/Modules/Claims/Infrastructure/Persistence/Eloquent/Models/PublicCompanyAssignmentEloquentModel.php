<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

/** @internal */
final class PublicCompanyAssignmentEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'public_company_assignments';

    protected $fillable = [
        'claim_id',
        'public_company_id',
        'assignment_date',
    ];

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return BelongsTo<PublicCompanyEloquentModel, $this> */
    public function publicCompany(): BelongsTo
    {
        return $this->belongsTo(PublicCompanyEloquentModel::class, 'public_company_id');
    }
}

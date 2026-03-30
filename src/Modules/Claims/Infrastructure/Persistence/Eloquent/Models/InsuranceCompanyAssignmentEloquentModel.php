<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

/** @internal */
final class InsuranceCompanyAssignmentEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'insurance_company_assignments';

    protected $fillable = [
        'claim_id',
        'insurance_company_id',
        'assignment_date',
    ];

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return BelongsTo<InsuranceCompanyEloquentModel, $this> */
    public function insuranceCompany(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompanyEloquentModel::class, 'insurance_company_id');
    }
}

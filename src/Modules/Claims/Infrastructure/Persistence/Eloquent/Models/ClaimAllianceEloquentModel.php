<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;

/** @internal */
final class ClaimAllianceEloquentModel extends Model
{
    protected $table = 'claim_alliances';

    protected $fillable = [
        'claim_id',
        'alliance_company_id',
        'assignment_date',
    ];

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return BelongsTo<AllianceCompanyEloquentModel, $this> */
    public function allianceCompany(): BelongsTo
    {
        return $this->belongsTo(AllianceCompanyEloquentModel::class, 'alliance_company_id');
    }
}

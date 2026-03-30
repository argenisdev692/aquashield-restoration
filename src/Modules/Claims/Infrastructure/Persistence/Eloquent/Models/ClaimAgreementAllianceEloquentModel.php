<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

/** @internal */
final class ClaimAgreementAllianceEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'claim_agreement_alliances';

    protected $fillable = [
        'uuid',
        'user_id',
        'claim_id',
        'alliance_company_id',
        'full_pdf_path',
    ];

    /** @return BelongsTo<ClaimEloquentModel, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimEloquentModel::class, 'claim_id');
    }

    /** @return BelongsTo<UserEloquentModel, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }

    /** @return BelongsTo<AllianceCompanyEloquentModel, $this> */
    public function allianceCompany(): BelongsTo
    {
        return $this->belongsTo(AllianceCompanyEloquentModel::class, 'alliance_company_id');
    }
}

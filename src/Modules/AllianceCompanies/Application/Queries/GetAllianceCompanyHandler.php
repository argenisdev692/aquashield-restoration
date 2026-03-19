<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\Queries;

use Modules\AllianceCompanies\Application\DTOs\AllianceCompanyData;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;

final class GetAllianceCompanyHandler
{
    public function handle(string $uuid): ?AllianceCompanyData
    {
        $allianceCompany = AllianceCompanyEloquentModel::query()
            ->withTrashed()
            ->select([
                'uuid',
                'alliance_company_name',
                'address',
                'phone',
                'email',
                'website',
                'user_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->where('uuid', $uuid)
            ->first();

        if ($allianceCompany === null) {
            return null;
        }

        return AllianceCompanyData::from([
            'uuid' => $allianceCompany->uuid,
            'allianceCompanyName' => $allianceCompany->alliance_company_name,
            'address' => $allianceCompany->address,
            'phone' => $allianceCompany->phone,
            'email' => $allianceCompany->email,
            'website' => $allianceCompany->website,
            'userId' => $allianceCompany->user_id,
            'createdAt' => $allianceCompany->created_at?->toIso8601String() ?? '',
            'updatedAt' => $allianceCompany->updated_at?->toIso8601String() ?? '',
            'deletedAt' => $allianceCompany->deleted_at?->toIso8601String(),
        ]);
    }
}

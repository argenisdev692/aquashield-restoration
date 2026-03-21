<?php

declare(strict_types=1);

namespace App\Models;

use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

class PublicCompany extends PublicCompanyEloquentModel
{
    public function publicAdjuster()
    {
        return $this->hasMany(PublicAdjuster::class, 'public_company_id');
    }

    public function publicCompanyAssignments()
    {
        return $this->hasMany(PublicCompanyAssignment::class);
    }
}

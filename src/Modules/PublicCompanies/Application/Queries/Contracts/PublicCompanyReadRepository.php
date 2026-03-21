<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;
use Modules\PublicCompanies\Application\Queries\ReadModels\PublicCompanyReadModel;

interface PublicCompanyReadRepository
{
    public function paginate(PublicCompanyFilterData $filters): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?PublicCompanyReadModel;
}

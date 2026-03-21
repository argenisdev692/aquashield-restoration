<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Application\Queries;

use Modules\PublicCompanies\Application\Queries\Contracts\PublicCompanyReadRepository;
use Modules\PublicCompanies\Application\Queries\ReadModels\PublicCompanyReadModel;

final class GetPublicCompanyHandler
{
    public function __construct(
        private readonly PublicCompanyReadRepository $repository,
    ) {}

    public function handle(string $uuid): ?PublicCompanyReadModel
    {
        return $this->repository->findByUuid($uuid);
    }
}

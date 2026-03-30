<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\Queries;

use Src\Modules\Claims\Application\Queries\Contracts\ClaimReadRepository;
use Src\Modules\Claims\Application\Queries\ReadModels\ClaimReadModel;

final class GetClaimHandler
{
    public function __construct(
        private readonly ClaimReadRepository $readRepository,
    ) {}

    public function handle(string $uuid): ?ClaimReadModel
    {
        return $this->readRepository->findByUuid($uuid);
    }
}

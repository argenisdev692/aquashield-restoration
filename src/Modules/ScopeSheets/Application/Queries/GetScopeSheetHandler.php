<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Application\Queries;

use Src\Modules\ScopeSheets\Application\Queries\Contracts\ScopeSheetReadRepository;
use Src\Modules\ScopeSheets\Application\Queries\ReadModels\ScopeSheetReadModel;

final class GetScopeSheetHandler
{
    public function __construct(
        private readonly ScopeSheetReadRepository $readRepository,
    ) {}

    public function handle(string $uuid): ?ScopeSheetReadModel
    {
        return $this->readRepository->findByUuid($uuid);
    }
}

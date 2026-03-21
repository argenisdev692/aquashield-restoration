<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\Commands;

use Src\Modules\ServiceCategories\Domain\Ports\ServiceCategoryRepositoryPort;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;

final class DeleteServiceCategoryHandler
{
    public function __construct(
        private readonly ServiceCategoryRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(ServiceCategoryId::fromString($uuid));
    }
}

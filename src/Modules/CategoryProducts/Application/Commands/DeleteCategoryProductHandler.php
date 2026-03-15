<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\Commands;

use Src\Modules\CategoryProducts\Domain\Ports\CategoryProductRepositoryPort;
use Src\Modules\CategoryProducts\Domain\ValueObjects\CategoryProductId;

final class DeleteCategoryProductHandler
{
    public function __construct(
        private readonly CategoryProductRepositoryPort $repository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->repository->softDelete(CategoryProductId::fromString($uuid));
    }
}

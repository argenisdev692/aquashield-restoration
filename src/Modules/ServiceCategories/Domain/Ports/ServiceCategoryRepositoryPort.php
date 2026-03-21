<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Domain\Ports;

use Src\Modules\ServiceCategories\Domain\Entities\ServiceCategory;
use Src\Modules\ServiceCategories\Domain\ValueObjects\ServiceCategoryId;

interface ServiceCategoryRepositoryPort
{
    public function find(ServiceCategoryId $id): ?ServiceCategory;

    public function save(ServiceCategory $serviceCategory): void;

    public function softDelete(ServiceCategoryId $id): void;

    public function restore(ServiceCategoryId $id): void;

    public function bulkSoftDelete(array $ids): int;
}

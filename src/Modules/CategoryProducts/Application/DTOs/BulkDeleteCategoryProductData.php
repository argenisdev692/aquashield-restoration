<?php

declare(strict_types=1);

namespace Src\Modules\CategoryProducts\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteCategoryProductData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}

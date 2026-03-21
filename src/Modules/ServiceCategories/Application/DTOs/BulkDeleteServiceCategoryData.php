<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteServiceCategoryData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}

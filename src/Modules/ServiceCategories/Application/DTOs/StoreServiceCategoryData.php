<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class StoreServiceCategoryData extends Data
{
    public function __construct(
        public string $category,
        public ?string $type = null,
    ) {}
}

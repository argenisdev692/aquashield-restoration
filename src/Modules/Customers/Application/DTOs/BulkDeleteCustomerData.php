<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class BulkDeleteCustomerData extends Data
{
    /**
     * @param array<int, string> $uuids
     */
    public function __construct(
        public array $uuids,
    ) {}
}

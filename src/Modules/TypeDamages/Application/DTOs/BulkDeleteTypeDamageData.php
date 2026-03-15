<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class BulkDeleteTypeDamageData extends Data
{
    /**
     * @param list<string> $uuids
     */
    public function __construct(
        public array $uuids,
    ) {}
}

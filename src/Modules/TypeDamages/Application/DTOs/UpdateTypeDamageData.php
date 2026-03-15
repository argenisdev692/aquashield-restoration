<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateTypeDamageData extends Data
{
    public function __construct(
        public string $typeDamageName,
        public ?string $description = null,
        public string $severity = 'low',
    ) {}
}

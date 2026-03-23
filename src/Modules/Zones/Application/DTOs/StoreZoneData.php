<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class StoreZoneData extends Data
{
    public function __construct(
        public string $zoneName,
        public string $zoneType = 'interior',
        public ?string $code = null,
        public ?string $description = null,
        public int $userId = 0,
    ) {}
}

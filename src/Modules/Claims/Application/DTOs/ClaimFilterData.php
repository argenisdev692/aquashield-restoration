<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class ClaimFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?int $claimStatusId = null,
        public ?int $typeDamageId = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}

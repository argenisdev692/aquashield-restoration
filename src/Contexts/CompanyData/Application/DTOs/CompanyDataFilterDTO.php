<?php

declare(strict_types=1);

namespace Src\Contexts\CompanyData\Application\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CompanyDataFilterDTO extends Data
{
    public function __construct(
        public ?int $page = 1,
        public ?int $perPage = 15,
        public ?string $search = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $sortBy = 'created_at',
        public ?string $sortDir = 'desc',
        public ?int $userId = null,
    ) {
    }
}


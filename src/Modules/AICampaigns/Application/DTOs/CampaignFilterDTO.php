<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Application\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CampaignFilterDTO extends Data
{
    public function __construct(
        public ?string $search    = null,
        public ?string $status    = null,
        public ?string $platform  = null,
        public ?string $dateFrom  = null,
        public ?string $dateTo    = null,
        public ?string $sortBy    = 'created_at',
        public ?string $sortDir   = 'desc',
        public int     $page      = 1,
        public int     $perPage   = 15,
    ) {
    }
}

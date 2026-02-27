<?php

declare(strict_types=1);

namespace Src\Contexts\Users\Application\DTOs;

/**
 * UserFilterDTO — Filters for paginated user listing.
 *
 * 🧬 readonly (PHP 8.5)
 */
final readonly class UserFilterDTO
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
        public ?string $search = null,
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public string $sortBy = 'created_at',
        public string $sortDir = 'desc',
    ) {
    }
}

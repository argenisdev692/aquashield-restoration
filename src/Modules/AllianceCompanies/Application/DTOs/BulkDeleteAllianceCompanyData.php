<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteAllianceCompanyData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}

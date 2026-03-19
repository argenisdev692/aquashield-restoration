<?php

declare(strict_types=1);

namespace Modules\EmailData\Application\DTOs;

use Spatie\LaravelData\Data;

final class BulkDeleteEmailDataData extends Data
{
    public function __construct(
        public array $uuids,
    ) {}
}

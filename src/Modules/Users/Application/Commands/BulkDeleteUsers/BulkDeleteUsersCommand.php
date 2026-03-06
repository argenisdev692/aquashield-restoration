<?php

declare(strict_types=1);

namespace Modules\Users\Application\Commands\BulkDeleteUsers;

final readonly class BulkDeleteUsersCommand
{
    public function __construct(
        public array $uuids,
    ) {
    }
}

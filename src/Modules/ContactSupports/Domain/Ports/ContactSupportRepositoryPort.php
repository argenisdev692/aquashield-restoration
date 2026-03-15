<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Domain\Ports;

use Src\Modules\ContactSupports\Domain\Entities\ContactSupport;
use Src\Modules\ContactSupports\Domain\ValueObjects\ContactSupportId;

interface ContactSupportRepositoryPort
{
    public function find(ContactSupportId $id): ?ContactSupport;

    public function save(ContactSupport $contactSupport): void;

    public function softDelete(ContactSupportId $id): void;

    public function restore(ContactSupportId $id): void;

    public function bulkSoftDelete(array $ids): int;
}

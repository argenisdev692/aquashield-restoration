<?php

declare(strict_types=1);

namespace Modules\EmailData\Domain\Ports;

use Modules\EmailData\Domain\Entities\EmailData;
use Modules\EmailData\Domain\ValueObjects\EmailDataId;

interface EmailDataRepositoryPort
{
    public function find(EmailDataId $id): ?EmailData;

    public function save(EmailData $emailData): void;

    public function softDelete(EmailDataId $id): void;

    public function restore(EmailDataId $id): void;

    public function bulkSoftDelete(array $ids): int;
}

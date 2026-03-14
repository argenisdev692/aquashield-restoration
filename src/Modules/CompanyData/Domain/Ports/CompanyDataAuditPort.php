<?php

declare(strict_types=1);

namespace Modules\CompanyData\Domain\Ports;

interface CompanyDataAuditPort
{
    public function log(string $logName, string $description, array $properties = [], mixed $subject = null): void;
}

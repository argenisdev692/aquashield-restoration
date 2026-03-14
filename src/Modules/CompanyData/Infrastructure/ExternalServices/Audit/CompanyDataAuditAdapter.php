<?php

declare(strict_types=1);

namespace Modules\CompanyData\Infrastructure\ExternalServices\Audit;

use Modules\CompanyData\Domain\Ports\CompanyDataAuditPort;
use Shared\Infrastructure\Audit\AuditInterface;

final readonly class CompanyDataAuditAdapter implements CompanyDataAuditPort
{
    public function __construct(
        private AuditInterface $audit,
    ) {
    }

    public function log(string $logName, string $description, array $properties = [], mixed $subject = null): void
    {
        $this->audit->log($logName, $description, $properties, $subject);
    }
}

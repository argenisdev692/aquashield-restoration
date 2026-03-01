<?php

declare(strict_types=1);

namespace Modules\AllianceCompanies\Domain\Events;

use Modules\AllianceCompanies\Domain\Entities\AllianceCompany;
use Shared\Domain\Events\DomainEvent;

final class AllianceCompanyUpdated extends DomainEvent
{
    public function __construct(
        public readonly AllianceCompany $AllianceCompany
    ) {
        parent::__construct($AllianceCompany->getId()->value);
    }

    public static function eventName(): string
    {
        return 'alliance_company.updated';
    }
}

<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Domain\Events;

use Modules\InsuranceCompanies\Domain\Entities\InsuranceCompany;
use Shared\Domain\Events\DomainEvent;

final class InsuranceCompanyCreated extends DomainEvent
{
    public function __construct(
        public readonly InsuranceCompany $insuranceCompany
    ) {
        parent::__construct($insuranceCompany->getId()->value);
    }

    public static function eventName(): string
    {
        return 'insurance_company.created';
    }
}

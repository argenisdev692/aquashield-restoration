<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Domain\Events;

use Modules\InsuranceCompanies\Domain\ValueObjects\InsuranceCompanyId;
use Shared\Domain\Events\DomainEvent;

final readonly class InsuranceCompanyDeleted extends DomainEvent
{
    public function __construct(
        public InsuranceCompanyId $id
    ) {
        parent::__construct($id->value);
    }

    public static function eventName(): string
    {
        return 'insurance_company.deleted';
    }
}
